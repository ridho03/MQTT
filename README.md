# Pengujian Performa MQTT

Pengujian dilakukan menggunakan program publisher Python (`mqtt_stress_test.py`) untuk mensimulasikan pengiriman data dari 500 kendaraan virtual ke broker MQTT melalui satu koneksi publisher. Data diterima oleh subscriber PHP, kemudian disimpan ke tabel `mqtt_test_results` pada MySQL/MariaDB.

Skenario pengujian:

| Payload/Kendaraan | Total Payload |
| ----------------: | ------------: |
|                 1 |           500 |
|                 2 |          1000 |
|                 5 |          2500 |
|                10 |          5000 |
|                20 |         10000 |

Parameter yang diukur:

* Packet Delivery Ratio (PDR)
* Packet Loss
* Latency (Minimum, Average, Maximum)
* Throughput Subscriber
* Jitter
* Publish Rate

---

## 1. Menampilkan Data Pengujian Terakhir

```sql
SELECT *
FROM mqtt_test_results
ORDER BY id DESC
LIMIT 100;
```

---

## 2. Menghitung Jumlah Payload yang Diterima

```sql
SELECT COUNT(*) AS payload_diterima
FROM mqtt_test_results
WHERE run_id = '20260618_163926_272485';
```

---

## 3. Menghitung Latency

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

## 4. Menghitung Packet Delivery Ratio (PDR) dan Packet Loss

```sql
SET @payload_dikirim = 500;

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
        /@payload_dikirim*100,
        2
    ) AS packet_loss_persen

FROM mqtt_test_results
WHERE run_id='20260618_163926_272485';
```

---

## 5. Menghitung Throughput Subscriber

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

## 6. Menghitung Ringkasan Pengujian (PDR, Packet Loss, Latency, Throughput, dan Jitter)

Gunakan query ringkasan yang telah dibuat untuk menghasilkan seluruh parameter pengujian dalam satu hasil, yaitu:

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

Query ini memanfaatkan Common Table Expression (CTE), fungsi agregasi SQL, dan fungsi `LAG()` untuk menghitung jitter berdasarkan selisih latency antar-payload yang diterima secara berurutan.

---

## 7. Verifikasi Payload Sensor

Jumlah payload yang berhasil disimpan pada tabel sensor dapat diverifikasi menggunakan query berikut.

```sql
SELECT
device_id,
COUNT(*) AS payload_per_vehicle
FROM sensor_data
WHERE device_id>='002'
GROUP BY device_id
ORDER BY device_id;
```

atau

```sql
SELECT COUNT(*) AS total_sensor
FROM sensor_data
WHERE device_id>='002';
```

---

## 8. Verifikasi Menggunakan Wireshark

Filter untuk melihat payload MQTT:

```text
mqtt.msgtype == 3
```

Filter untuk melihat proses koneksi MQTT:

```text
mqtt.msgtype == 1
```

Wireshark digunakan untuk memverifikasi bahwa payload berhasil dikirim melalui jaringan serta mengamati ukuran frame MQTT selama proses pengujian.
