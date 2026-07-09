import os
import glob
import csv
from datetime import datetime, timezone, timedelta
from statistics import mean

PUBLISHER_FOLDER = "publisher_logs"
RECEIVER_FOLDER = "vps2_received_logs"

# EC2 biasanya UTC.
# Kalau VPS kamu pakai WIB, ubah jadi: timezone(timedelta(hours=7))
VPS_RECEIVED_TIMEZONE = timezone.utc

# Kalau laptop kamu WIB, tidak perlu diatur karena send_timestamp_ns dari time.time_ns()
# sudah berbasis epoch universal.
VEHICLE_COUNT = 500


def parse_received_at(received_at):
    """
    Format dari vps2_subscriber.py:
    2026-07-07 13:53:52.123456
    """
    dt = datetime.strptime(received_at, "%Y-%m-%d %H:%M:%S.%f")
    dt = dt.replace(tzinfo=VPS_RECEIVED_TIMEZONE)
    return int(dt.timestamp() * 1_000_000_000)


def read_publisher_files():
    sent = {}

    for file in glob.glob(os.path.join(PUBLISHER_FOLDER, "*.csv")):
        with open(file, newline="", encoding="utf-8") as f:
            reader = csv.DictReader(f)

            for row in reader:
                message_id = row.get("message_id", "").strip()
                if not message_id:
                    continue

                sent[message_id] = {
                    "run_id": row.get("run_id", ""),
                    "device_id": row.get("device_id", ""),
                    "payload_number": row.get("payload_number", ""),
                    "send_timestamp_ns": int(row.get("send_timestamp_ns", "0")),
                    "raw_payload": row.get("raw_payload", ""),
                    "topic": row.get("topic", "")
                }

    return sent


def read_receiver_files():
    received = {}

    for file in glob.glob(os.path.join(RECEIVER_FOLDER, "*.csv")):
        with open(file, newline="", encoding="utf-8") as f:
            reader = csv.DictReader(f)

            for row in reader:
                message_id = row.get("message_id", "").strip()
                if not message_id:
                    continue

                # Hindari duplicate kalau ada data ganda
                if message_id in received:
                    continue

                received_at = row.get("received_at", "")
                received_timestamp_ns = parse_received_at(received_at)

                received[message_id] = {
                    "received_at": received_at,
                    "received_timestamp_ns": received_timestamp_ns,
                    "run_id": row.get("run_id", ""),
                    "device_id": row.get("device_id", ""),
                    "payload_number": row.get("payload_number", ""),
                    "send_timestamp_ns": int(row.get("send_timestamp_ns", "0")),
                    "raw_payload": row.get("raw_payload", ""),
                    "topic": row.get("topic", "")
                }

    return received


def get_payload_per_vehicle(run_id):
    try:
        return int(run_id.split("_")[-1].replace("PPV", ""))
    except Exception:
        return 0


def format_id_number(value):
    return f"{value:,}".replace(",", ".")


def format_decimal(value, digits=2):
    return f"{value:.{digits}f}".replace(".", ",")


def main():
    sent = read_publisher_files()
    received = read_receiver_files()

    runs = {}

    for message_id, data in sent.items():
        run_id = data["run_id"]

        if run_id not in runs:
            runs[run_id] = {
                "sent_ids": set(),
                "received_ids": set(),
                "send_timestamps": [],
                "latencies_ms": [],
                "payload_bytes": []
            }

        runs[run_id]["sent_ids"].add(message_id)
        runs[run_id]["send_timestamps"].append(data["send_timestamp_ns"])

    for message_id, data in received.items():
        run_id = data["run_id"]

        if run_id not in runs:
            continue

        if message_id in sent:
            send_ns = sent[message_id]["send_timestamp_ns"]
            receive_ns = data["received_timestamp_ns"]

            latency_ms = (receive_ns - send_ns) / 1_000_000

            runs[run_id]["received_ids"].add(message_id)
            runs[run_id]["latencies_ms"].append(latency_ms)
            runs[run_id]["payload_bytes"].append(len(data["raw_payload"].encode("utf-8")))

    print("\nTABEL 1 - HASIL PENGUJIAN BEBAN SISTEM KOMUNIKASI MQTT")
    print("-" * 120)
    print("Payload/Kendaraan | Total Payload | Payload Berhasil | PDR (%) | Packet Loss (%) | Durasi Publish (s) | Publish Rate")
    print("-" * 120)

    table1 = []

    for run_id in sorted(runs.keys(), key=get_payload_per_vehicle):
        data = runs[run_id]

        payload_per_vehicle = get_payload_per_vehicle(run_id)
        total_sent = len(data["sent_ids"])
        total_received = len(data["received_ids"])

        pdr = (total_received / total_sent * 100) if total_sent else 0
        packet_loss = 100 - pdr

        send_timestamps = data["send_timestamps"]

        if send_timestamps:
            duration = (max(send_timestamps) - min(send_timestamps)) / 1_000_000_000
        else:
            duration = 0

        publish_rate = total_sent / duration if duration > 0 else 0

        table1.append([
            payload_per_vehicle,
            total_sent,
            total_received,
            pdr,
            packet_loss,
            duration,
            publish_rate
        ])

        print(
            f"{payload_per_vehicle:^18} | "
            f"{format_id_number(total_sent):^13} | "
            f"{format_id_number(total_received):^16} | "
            f"{format_decimal(pdr, 2):^7} | "
            f"{format_decimal(packet_loss, 2):^15} | "
            f"{format_decimal(duration, 2):^18} | "
            f"{format_decimal(publish_rate, 2)}"
        )

    print("\n\nTABEL 2 - HASIL PENGUKURAN KINERJA SISTEM KOMUNIKASI MQTT")
    print("-" * 120)
    print("Total Payload | Latency Rata-rata (ms) | Latency Minimum (ms) | Latency Maksimum (ms) | Throughput (bit/s) | Jitter Rata-rata (ms)")
    print("-" * 120)

    table2 = []

    for run_id in sorted(runs.keys(), key=get_payload_per_vehicle):
        data = runs[run_id]

        total_received = len(data["received_ids"])
        latencies = data["latencies_ms"]
        payload_bytes = data["payload_bytes"]
        send_timestamps = data["send_timestamps"]

        if not latencies:
            continue

        latency_avg = mean(latencies)
        latency_min = min(latencies)
        latency_max = max(latencies)

        if len(latencies) > 1:
            jitter_values = [
                abs(latencies[i] - latencies[i - 1])
                for i in range(1, len(latencies))
            ]
            jitter_avg = mean(jitter_values)
        else:
            jitter_avg = 0

        if send_timestamps:
            duration = (max(send_timestamps) - min(send_timestamps)) / 1_000_000_000
        else:
            duration = 0

        total_bits = sum(payload_bytes) * 8
        throughput = total_bits / duration if duration > 0 else 0

        table2.append([
            total_received,
            latency_avg,
            latency_min,
            latency_max,
            throughput,
            jitter_avg
        ])

        print(
            f"{format_id_number(total_received):^13} | "
            f"{format_decimal(latency_avg, 3):^24} | "
            f"{format_decimal(latency_min, 3):^20} | "
            f"{format_decimal(latency_max, 3):^21} | "
            f"{format_decimal(throughput, 3):^18} | "
            f"{format_decimal(jitter_avg, 3)}"
        )

    print("\nSelesai.")


if __name__ == "__main__":
    main()