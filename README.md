# SIPK (Sistem Informasi Pemantauan Kendaraan)

SIPK (Sistem Informasi Pemantauan Kendaraan) merupakan aplikasi berbasis Laravel yang dikembangkan untuk memantau data emisi kendaraan secara **real-time** menggunakan teknologi **Internet of Things (IoT)** dan protokol **MQTT**. Sistem menerima data sensor dari perangkat IoT melalui broker MQTT, kemudian subscriber memproses dan menyimpan data ke dalam basis data MySQL sehingga dapat ditampilkan pada dashboard web.

---

# Fitur

* Monitoring emisi kendaraan secara real-time.
* Komunikasi data menggunakan MQTT.
* Penyimpanan data ke MySQL.
* Dashboard berbasis Laravel.
* Login Administrator dan User.
* Monitoring lokasi kendaraan (GPS).
* Pengujian performa komunikasi MQTT.

---

# Arsitektur Sistem

```
IoT Device / Python Publisher
            │
            ▼
      Mosquitto Broker
            │
            ▼
      PHP MQTT Subscriber
            │
            ▼
      MySQL 
            │
            ▼
      Laravel Dashboard
```

---

# Teknologi

| Komponen        | Teknologi       |
| --------------- | --------------- |
| Backend         | Laravel         |
| Bahasa          | PHP 8           |
| MQTT Broker     | Mosquitto       |
| MQTT Client     | phpMQTT         |
| Database        | MySQL           |
| Publisher       | Python 3        |
| Packet Analyzer | Wireshark       |
| Web Server      | Apache (XAMPP)  |

---

# Instalasi

Clone repository

```bash
git clone https://github.com/username/project.git
cd project
```

Install dependency

```bash
composer install
```

Copy file environment

```bash
cp .env.example .env
```

Generate application key

```bash
php artisan key:generate
```

Migrasi database

```bash
php artisan migrate
```

---

# Menjalankan Sistem

## 1. Jalankan Laravel

```bash
php artisan serve
```

atau akses

```
http://localhost/cobaMQTT/public/
```

---

## 2. Jalankan Broker MQTT

Masuk ke folder Mosquitto

```bash
cd "C:\Program Files\Mosquitto"
```

Menjalankan broker

```bash
mosquitto -v
```

Menggunakan autentikasi

```bash
mosquitto -c mosquitto.conf -v
```

Melihat payload yang diterima broker

```bash
mosquitto_sub -h localhost -t sipk/device1/data -v
```

---

## 3. Jalankan Subscriber

```bash
cd C:\xampp\htdocs\mqtt
php mqtt_data_sub.php
```

---

## 4. Jalankan Publisher

```bash
python mqtt_stress_test.py
```

Program publisher mensimulasikan 500 kendaraan virtual dan mengirim payload ke broker MQTT.

---

# Login

## Administrator

Email

```
admin1@gmail.com
```

Password

```
11111111
```

## User

Email

```
user1@gmail.com
```

Password

```
11111111
```

---

# Pengujian Performa MQTT

Pengujian dilakukan menggunakan program publisher Python (`mqtt_stress_test.py`) untuk mensimulasikan pengiriman data kendaraan ke broker MQTT.

Alur pengujian:

```
  Publisher Python
        │
        ▼
 Broker Mosquitto
        │
        ▼
 Subscriber PHP
        │
        ▼
      MySQL
        │
        ▼
    Query SQL
        │
        ▼
Perhitungan:
Latency
Throughput
PDR
Packet Loss
Jitter
```

Seluruh hasil pengujian disimpan pada tabel

```
mqtt_test_results
```

---

# Skenario Pengujian

| Payload/Kendaraan | Total Payload |
| ----------------: | ------------: |
|                 1 |           500 |
|                 2 |          1000 |
|                 5 |          2500 |
|                10 |          5000 |
|                20 |         10000 |

Parameter yang diukur

* Latency
* Throughput
* Packet Delivery Ratio (PDR)
* Packet Loss
* Jitter

---

# Query Pengujian

## Menampilkan data pengujian

```sql
SELECT *
FROM mqtt_test_results
ORDER BY id DESC
LIMIT 100;
```

---

## Menghitung jumlah payload diterima

```sql
SELECT COUNT(*) AS payload_diterima
FROM mqtt_test_results
WHERE run_id='20260618_163926_272485';
```

---

## Menghitung Latency

```sql
SELECT
run_id,
COUNT(*) AS payload_diterima,

ROUND(AVG(latency_ms),3) AS latency_rata_rata_ms,
ROUND(MIN(latency_ms),3) AS latency_minimum_ms,
ROUND(MAX(latency_ms),3) AS latency_maksimum_ms

FROM mqtt_test_results

WHERE run_id='20260618_163926_272485'

GROUP BY run_id;
```

---

## Menghitung Packet Delivery Ratio (PDR)

```sql
SET @payload_dikirim=500;

SELECT

@payload_dikirim AS payload_dikirim,

COUNT(*) AS payload_diterima,

@payload_dikirim-COUNT(*) AS payload_hilang,

ROUND(
COUNT(*)/@payload_dikirim*100,
2
) AS pdr_persen,

ROUND(
(@payload_dikirim-COUNT(*))
/
@payload_dikirim*100,
2
) AS packet_loss_persen

FROM mqtt_test_results

WHERE run_id='20260618_163926_272485';
```

---

## Menghitung Throughput Subscriber

```sql
SELECT

run_id,

COUNT(*) AS payload_diterima,

ROUND(

(

MAX(receive_timestamp_ns)

-

MIN(receive_timestamp_ns)

)/1000000000,

4

) AS durasi_penerimaan_detik,

ROUND(

COUNT(*)/

NULLIF(

(

MAX(receive_timestamp_ns)

-

MIN(receive_timestamp_ns)

)/1000000000,

0

),

2

) AS laju_penerimaan_payload_per_detik,

ROUND(

SUM(payload_size_bytes)*8/

NULLIF(

(

MAX(receive_timestamp_ns)

-

MIN(receive_timestamp_ns)

)/1000000000,

0

),

2

) AS throughput_subscriber_bit_per_detik

FROM mqtt_test_results

WHERE run_id='20260618_163926_272485'

GROUP BY run_id;
```

---

## Menghitung Seluruh Parameter Sekaligus

Gunakan query ringkasan yang telah dibuat untuk memperoleh:

* Payload dikirim
* Payload diterima
* Payload hilang
* Packet Delivery Ratio (PDR)
* Packet Loss
* Latency minimum
* Latency rata-rata
* Latency maksimum
* Durasi penerimaan
* Throughput subscriber
* Jitter rata-rata

---
# Verifikasi Wireshark

Melihat payload MQTT

```
mqtt.msgtype == 3
```

Melihat koneksi MQTT

```
mqtt.msgtype == 1
```

---

# Struktur Direktori

```
project

├── app

├── database

├── public

├── resources

├── routes

├── mqtt

│ ├── mqtt_data_sub.php

│ ├── broker_test.php

│ └── mqtt_stress_test.py

├── README.md
```

---

