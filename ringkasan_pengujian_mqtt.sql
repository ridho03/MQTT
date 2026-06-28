SET @payload_dikirim = 500;

WITH data_latency AS (
    SELECT
        run_id,
        message_id,
        payload_size_bytes,
        receive_timestamp_ns,
        latency_ms,
        LAG(latency_ms) OVER (
            PARTITION BY run_id
            ORDER BY receive_timestamp_ns, message_id
        ) AS latency_sebelumnya
    FROM mqtt_test_results
    WHERE BINARY run_id = BINARY '20260618_163926_272485'
),

ringkasan AS (
    SELECT
        run_id,
        COUNT(DISTINCT message_id) AS payload_diterima,

        ROUND(MIN(latency_ms), 3)
            AS latency_minimum_ms,

        ROUND(AVG(latency_ms), 3)
            AS latency_rata_rata_ms,

        ROUND(MAX(latency_ms), 3)
            AS latency_maksimum_ms,

        ROUND(
            (
                MAX(receive_timestamp_ns)
                - MIN(receive_timestamp_ns)
            ) / 1000000000.0,
            4
        ) AS durasi_penerimaan_detik,

        ROUND(
            COUNT(DISTINCT message_id) /
            NULLIF(
                (
                    MAX(receive_timestamp_ns)
                    - MIN(receive_timestamp_ns)
                ) / 1000000000.0,
                0
            ),
            2
        ) AS laju_penerimaan_payload_per_detik,

        ROUND(
            SUM(payload_size_bytes) * 8.0 /
            NULLIF(
                (
                    MAX(receive_timestamp_ns)
                    - MIN(receive_timestamp_ns)
                ) / 1000000000.0,
                0
            ),
            2
        ) AS throughput_subscriber_bit_per_detik,

        ROUND(
            AVG(
                CASE
                    WHEN latency_sebelumnya IS NOT NULL
                    THEN ABS(latency_ms - latency_sebelumnya)
                END
            ),
            3
        ) AS jitter_rata_rata_ms

    FROM data_latency
    GROUP BY run_id
)

SELECT parameter, nilai
FROM (
    SELECT
        1 AS urutan,
        'Run ID' AS parameter,
        CAST(run_id AS CHAR) AS nilai
    FROM ringkasan

    UNION ALL

    SELECT
        2,
        'Payload dikirim',
        CAST(@payload_dikirim AS CHAR)
    FROM ringkasan

    UNION ALL

    SELECT
        3,
        'Payload diterima',
        CAST(payload_diterima AS CHAR)
    FROM ringkasan

    UNION ALL

    SELECT
        4,
        'Payload hilang',
        CAST(@payload_dikirim - payload_diterima AS CHAR)
    FROM ringkasan

    UNION ALL

    SELECT
        5,
        'Packet Delivery Ratio (PDR)',
        CONCAT(
            ROUND(
                payload_diterima * 100.0 /
                NULLIF(@payload_dikirim, 0),
                2
            ),
            ' %'
        )
    FROM ringkasan

    UNION ALL

    SELECT
        6,
        'Packet loss',
        CONCAT(
            ROUND(
                (
                    @payload_dikirim - payload_diterima
                ) * 100.0 /
                NULLIF(@payload_dikirim, 0),
                2
            ),
            ' %'
        )
    FROM ringkasan

    UNION ALL

    SELECT
        7,
        'Latency minimum',
        CONCAT(latency_minimum_ms, ' ms')
    FROM ringkasan

    UNION ALL

    SELECT
        8,
        'Latency rata-rata',
        CONCAT(latency_rata_rata_ms, ' ms')
    FROM ringkasan

    UNION ALL

    SELECT
        9,
        'Latency maksimum',
        CONCAT(latency_maksimum_ms, ' ms')
    FROM ringkasan

    UNION ALL

    SELECT
        10,
        'Durasi penerimaan',
        CONCAT(durasi_penerimaan_detik, ' detik')
    FROM ringkasan

    UNION ALL

    SELECT
        11,
        'Laju penerimaan',
        CONCAT(
            laju_penerimaan_payload_per_detik,
            ' payload/detik'
        )
    FROM ringkasan

    UNION ALL

    SELECT
        12,
        'Throughput subscriber',
        CONCAT(
            throughput_subscriber_bit_per_detik,
            ' bit/detik'
        )
    FROM ringkasan

    UNION ALL

    SELECT
        13,
        'Jitter rata-rata',
        CONCAT(jitter_rata_rata_ms, ' ms')
    FROM ringkasan
) AS hasil
ORDER BY urutan;