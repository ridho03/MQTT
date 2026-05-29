# ============================================================
# MQTT MULTI-VEHICLE STRESS TEST
# Simulasi pengiriman data MQTT menggunakan
# 500 kendaraan virtual.
# ============================================================

import paho.mqtt.client as mqtt
import time
from datetime import datetime
import random

# ============================================================
# KONFIGURASI MQTT
# ============================================================

BROKER = "127.0.0.1"

PORT = 1883

TOPIC = "sipk/device1/data"

# ============================================================
# KONFIGURASI PENGUJIAN
# ============================================================

TOTAL_VEHICLES = 500

PAYLOAD_PER_VEHICLE = 1

DELAY = 0.005

# ============================================================
# MQTT CLIENT
# ============================================================

client = mqtt.Client()

# AUTHENTICATION MQTT
client.username_pw_set(
    "mqttuser",
    "12345678"
)

print("Connecting Broker...")

client.connect(
    BROKER,
    PORT,
    60
)

print("MQTT CONNECTED\n")

# ============================================================
# COUNTER
# ============================================================

total_sent = 0

success = 0

failed = 0

# ============================================================
# TIMER START
# ============================================================

start_time = time.time()

# ============================================================
# LOOP KENDARAAN
# ============================================================

for vehicle in range(2,502):

    device_id=f"{vehicle:03}"

    for i in range(PAYLOAD_PER_VEHICLE):

        # ==========================================
        # SIMULASI DATA SENSOR
        # ==========================================

        humidity=round(
            random.uniform(70,90),2
        )

        tempC=round(
            random.uniform(29,35),2
        )

        tempF=round(
            (tempC*9/5)+32,
            2
        )

        coPPM=round(
            random.uniform(20,35),2
        )

        nh3PPM=round(
            random.uniform(1,2),2
        )

        no2PPM=round(
            random.uniform(0.1,0.5),2
        )

        hidrocarbon=round(
            random.uniform(5,10),2
        )

        latitude=-6.200000

        longitude=106.816666

        speed=round(
            random.uniform(10,60),2
        )

        pm=round(
            random.uniform(0.5,2),2
        )

        flow=round(
            random.uniform(15,25),2
        )

        date=datetime.now().strftime(
            "%d/%m/%Y"
        )

        current_time=datetime.now().strftime(
            "%H:%M:%S"
        )

        # ==========================================
        # PEMBENTUKAN PAYLOAD CSV
        # ==========================================

        payload=(

            f"{device_id},"

            f"{humidity},"

            f"{tempC},"

            f"{tempF},"

            f"{coPPM},"

            f"{nh3PPM},"

            f"{no2PPM},"

            f"{hidrocarbon},"

            f"{latitude},"

            f"{longitude},"

            f"{speed},"

            f"{pm},"

            f"{flow},"

            f"{date},"

            f"{current_time}"

        )

        # ==========================================
        # SIZE ANALYSIS
        # ==========================================

        csv_payload_size = len(
            payload.encode()
        )

        topic_size = len(
            TOPIC.encode()
        )

        mqtt_header = 2

        mqtt_message_size = (

            csv_payload_size +

            topic_size +

            mqtt_header

        )

        tcp_header = 20

        ip_header = 20

        loopback_header = 26

        network_packet_size = (

            mqtt_message_size +

            tcp_header +

            ip_header +

            loopback_header

        )

        # ==========================================
        # MQTT PUBLISH
        # ==========================================

        result = client.publish(
            TOPIC,
            payload
        )

        total_sent +=1

        if(
            result.rc
            ==
            mqtt.MQTT_ERR_SUCCESS
        ):

            success +=1

        else:

            failed +=1

        # progress output

        if total_sent % 100 == 0:

            print(
                f"Progress : "
                f"{total_sent} payload"
            )

        time.sleep(DELAY)

# ============================================================
# TIMER STOP
# ============================================================

end_time=time.time()

duration=end_time-start_time

# ============================================================
# DISCONNECT
# ============================================================

client.disconnect()

# ============================================================
# RESULT
# ============================================================

print("\n========== TEST RESULT ==========\n")

print(
f"Vehicle Tested          : "
f"{TOTAL_VEHICLES}"
)

print(
f"Payload/Vehicle         : "
f"{PAYLOAD_PER_VEHICLE}"
)

print(
f"Total Payload           : "
f"{total_sent}"
)

print()

print(
f"Payload Success         : "
f"{success}"
)

print(
f"Payload Failed          : "
f"{failed}"
)

print()

print(
"========== DATA SIZE ANALYSIS ==========\n"
)

print(
f"CSV Payload Size        : "
f"{csv_payload_size} bytes"
)

print(
f"MQTT Message Size       : "
f"{mqtt_message_size} bytes"
)

print(
f"Network Packet Size     : "
f"{network_packet_size} bytes"
)

print()

print(
f"Duration                : "
f"{duration:.2f} sec"
)

print(
f"Publish Rate            : "
f"{total_sent/duration:.2f} payload/sec"
)

print("\nMQTT DISCONNECTED")