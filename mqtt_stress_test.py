# ============================================================
# MQTT STRESS TEST PUBLISHER
# Simulasi pengiriman payload MQTT menggunakan Python
# Menggantikan Arduino UNO R4 WiFi sebagai publisher
# ============================================================

import paho.mqtt.client as mqtt
import time
from datetime import datetime
import random

# ============================================================
# KONFIGURASI MQTT
# Samakan dengan konfigurasi Arduino
# ============================================================

BROKER = "172.20.10.5"          # IP Mosquitto Broker
PORT = 1883                     # Port MQTT
TOPIC = "sipk/device1/data"     # Topic MQTT

# jumlah payload yang akan diuji
TOTAL_PAYLOAD = 100

# delay antar publish (detik)
DELAY = 0.01

# ============================================================
# MEMBUAT MQTT CLIENT
# ============================================================

client = mqtt.Client()

print("Connecting to broker...")

# koneksi ke broker
client.connect(BROKER, PORT, 60)

print("MQTT CONNECTED\n")

# ============================================================
# LOOP PENGIRIMAN PAYLOAD
# ============================================================

for i in range(TOTAL_PAYLOAD):

    # --------------------------------------------------------
    # SIMULASI DATA SENSOR
    # dibuat random supaya mirip data Arduino asli
    # --------------------------------------------------------

    humidity = round(random.uniform(70,90),2)

    tempC = round(random.uniform(29,35),2)

    tempF = round((tempC*9/5)+32,2)

    coPPM = round(random.uniform(20,35),2)

    nh3PPM = round(random.uniform(1,2),2)

    no2PPM = round(random.uniform(0.1,0.5),2)

    hidrocarbon = round(random.uniform(5,10),2)

    latitude = -6.200000

    longitude = 106.816666

    speed = round(random.uniform(10,60),2)

    pm = round(random.uniform(0.5,2),2)

    flow = round(random.uniform(15,25),2)

    # waktu realtime
    date = datetime.now().strftime("%d/%m/%Y")

    current_time = datetime.now().strftime("%H:%M:%S")

    # --------------------------------------------------------
    # PEMBENTUKAN PAYLOAD CSV
    # format disamakan dengan Arduino UNO R4 WiFi
    # --------------------------------------------------------

    payload = (

        f"001,"                 # device_id

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

    # --------------------------------------------------------
    # MENGHITUNG UKURAN PAYLOAD (BYTE)
    # untuk analisis komunikasi data
    # --------------------------------------------------------

    payload_size = len(payload.encode('utf-8'))

    # --------------------------------------------------------
    # PUBLISH PAYLOAD KE BROKER MQTT
    # --------------------------------------------------------

    client.publish(TOPIC,payload)

    print(f"[{i+1}] SENT | {payload_size} bytes")

    # delay antar payload
    time.sleep(DELAY)

# ============================================================
# DISCONNECT
# ============================================================

client.disconnect()

print("\nMQTT DISCONNECTED")