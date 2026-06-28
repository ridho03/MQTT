
# ============================================================
# MQTT MULTI-VEHICLE STRESS TEST
# 500 kendaraan virtual melalui satu koneksi MQTT
# ============================================================

import csv
import random
import time
from datetime import datetime
from pathlib import Path

import paho.mqtt.client as mqtt


# ============================================================
# KONFIGURASI MQTT
# ============================================================

BROKER = "127.0.0.1"
PORT = 1883
TOPIC = "sipk/device1/data"

MQTT_USERNAME = "mqttuser"
MQTT_PASSWORD = "12345678"


# ============================================================
# KONFIGURASI PENGUJIAN
# Ubah menjadi 1, 2, 5, 10, atau 20
# ============================================================

TOTAL_VEHICLES = 500
PAYLOAD_PER_VEHICLE = 20
DELAY = 0.005

RUN_ID = datetime.now().strftime("%Y%m%d_%H%M%S_%f")
LOG_FILE = Path(f"publisher_log_{RUN_ID}.csv")


# ============================================================
# MQTT CLIENT
# ============================================================

client = mqtt.Client(
    client_id=f"PY_STRESS_{RUN_ID}",
    protocol=mqtt.MQTTv311
)

client.username_pw_set(
    MQTT_USERNAME,
    MQTT_PASSWORD
)

print("Menghubungkan ke broker MQTT...")

client.connect(
    BROKER,
    PORT,
    keepalive=60
)

client.loop_start()

# Menunggu koneksi aktif
connection_timeout = time.time() + 5

while not client.is_connected():
    if time.time() > connection_timeout:
        client.loop_stop()
        raise ConnectionError("Gagal terhubung ke broker MQTT.")

    time.sleep(0.05)

print("MQTT CONNECTED\n")


# ============================================================
# COUNTER DAN LOG
# ============================================================

total_attempted = 0
published_by_client = 0
failed_publish = 0
total_payload_bytes = 0

publisher_log = []

start_time = time.perf_counter()


# ============================================================
# SIMULASI 500 KENDARAAN VIRTUAL
# device_id 002 sampai 501
# ============================================================

for vehicle in range(2, 502):

    device_id = f"{vehicle:03}"

    for payload_number in range(
        1,
        PAYLOAD_PER_VEHICLE + 1
    ):

        # ----------------------------------------------------
        # SIMULASI DATA SENSOR
        # ----------------------------------------------------

        humidity = round(
            random.uniform(70, 90),
            2
        )

        temp_c = round(
            random.uniform(29, 35),
            2
        )

        temp_f = round(
            (temp_c * 9 / 5) + 32,
            2
        )

        co_ppm = round(
            random.uniform(20, 35),
            2
        )

        nh3_ppm = round(
            random.uniform(1, 2),
            2
        )

        no2_ppm = round(
            random.uniform(0.1, 0.5),
            2
        )

        hydrocarbon_ppm = round(
            random.uniform(5, 10),
            2
        )

        latitude = -6.200000
        longitude = 106.816666

        speed_kmph = round(
            random.uniform(10, 60),
            2
        )

        pm_density = round(
            random.uniform(0.5, 2),
            2
        )

        flow_sensor = round(
            random.uniform(15, 25),
            2
        )

        device_date = datetime.now().strftime(
            "%d/%m/%Y"
        )

        device_time = datetime.now().strftime(
            "%H:%M:%S"
        )

        # ----------------------------------------------------
        # IDENTITAS KHUSUS PENGUJIAN
        # ----------------------------------------------------

        message_id = (
            f"{RUN_ID}-"
            f"{device_id}-"
            f"{payload_number:02d}"
        )

        # Waktu epoch dalam nanodetik
        send_timestamp_ns = time.time_ns()

        # ----------------------------------------------------
        # PAYLOAD CSV
        #
        # Field 1-15  : data utama
        # Field 16    : message_id
        # Field 17    : send_timestamp_ns
        # ----------------------------------------------------

        fields = [
            device_id,
            humidity,
            temp_c,
            temp_f,
            co_ppm,
            nh3_ppm,
            no2_ppm,
            hydrocarbon_ppm,
            latitude,
            longitude,
            speed_kmph,
            pm_density,
            flow_sensor,
            device_date,
            device_time,
            message_id,
            send_timestamp_ns
        ]

        payload = ",".join(
            map(str, fields)
        )

        payload_size_bytes = len(
            payload.encode("utf-8")
        )

        # ----------------------------------------------------
        # MQTT PUBLISH
        # ----------------------------------------------------

        publish_info = client.publish(
            topic=TOPIC,
            payload=payload,
            qos=0,
            retain=False
        )

        total_attempted += 1
        total_payload_bytes += payload_size_bytes

        published = False

        try:
            publish_info.wait_for_publish(
                timeout=5
            )

            published = (
                publish_info.rc
                == mqtt.MQTT_ERR_SUCCESS
                and publish_info.is_published()
            )

        except (RuntimeError, ValueError):
            published = False

        if published:
            published_by_client += 1
        else:
            failed_publish += 1

        publisher_log.append({
            "run_id": RUN_ID,
            "message_id": message_id,
            "device_id": device_id,
            "payload_number": payload_number,
            "send_timestamp_ns": send_timestamp_ns,
            "payload_size_bytes": payload_size_bytes,
            "published_by_client": int(published)
        })

        if total_attempted % 100 == 0:
            print(
                f"Progress: "
                f"{total_attempted} payload"
            )

        time.sleep(DELAY)


# ============================================================
# TIMER STOP
# ============================================================

end_time = time.perf_counter()
duration = end_time - start_time


# ============================================================
# SIMPAN LOG PUBLISHER KE CSV
# ============================================================

with LOG_FILE.open(
    mode="w",
    newline="",
    encoding="utf-8"
) as csv_file:

    writer = csv.DictWriter(
        csv_file,
        fieldnames=[
            "run_id",
            "message_id",
            "device_id",
            "payload_number",
            "send_timestamp_ns",
            "payload_size_bytes",
            "published_by_client"
        ]
    )

    writer.writeheader()
    writer.writerows(publisher_log)


# ============================================================
# DISCONNECT
# ============================================================

client.disconnect()
client.loop_stop()


# ============================================================
# HASIL PUBLISHER
# ============================================================

publisher_rate = (
    published_by_client / duration
    if duration > 0
    else 0
)

publisher_throughput_bps = (
    total_payload_bytes * 8 / duration
    if duration > 0
    else 0
)

print("\n========== TEST RESULT ==========\n")

print(f"Run ID                    : {RUN_ID}")
print(f"Vehicle Tested            : {TOTAL_VEHICLES}")
print(f"Payload/Vehicle           : {PAYLOAD_PER_VEHICLE}")
print(f"Total Payload             : {total_attempted}")

print()

print(f"Published by Client       : {published_by_client}")
print(f"Failed Publish            : {failed_publish}")

print()

print(f"Total Payload Size        : {total_payload_bytes} bytes")
print(f"Duration                  : {duration:.4f} sec")
print(
    f"Publisher Rate            : "
    f"{publisher_rate:.2f} payload/sec"
)
print(
    f"Publisher Throughput      : "
    f"{publisher_throughput_bps:.2f} bit/sec"
)

print()

print(f"Publisher Log             : {LOG_FILE.resolve()}")
print("\nMQTT DISCONNECTED")