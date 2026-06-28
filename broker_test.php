```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/phpMQTT.php';

use Bluerhinos\phpMQTT;


/* ============================================================
   KONFIGURASI DATABASE
   ============================================================ */

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'carbon2025';

$db = new mysqli(
    $dbHost,
    $dbUser,
    $dbPass,
    $dbName
);

if ($db->connect_errno) {
    exit(
        'Koneksi database gagal: '
        . $db->connect_error
        . PHP_EOL
    );
}

$db->set_charset('utf8mb4');


/* ============================================================
   TABEL KHUSUS HASIL PENGUJIAN
   Tidak mengubah tabel utama SIPK
   ============================================================ */

$createTableSql = "
CREATE TABLE IF NOT EXISTS mqtt_test_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_id VARCHAR(32) NOT NULL,
    message_id VARCHAR(100) NOT NULL,
    device_id VARCHAR(20) NOT NULL,
    send_timestamp_ns BIGINT UNSIGNED NOT NULL,
    receive_timestamp_ns BIGINT UNSIGNED NOT NULL,
    latency_ms DECIMAL(15,3) NOT NULL,
    payload_size_bytes INT UNSIGNED NOT NULL,
    raw_payload TEXT NOT NULL,
    received_at DATETIME(6) NOT NULL,
    UNIQUE KEY unique_message_id (message_id),
    INDEX index_run_id (run_id),
    INDEX index_device_id (device_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if (!$db->query($createTableSql)) {
    exit(
        'Gagal membuat tabel pengujian: '
        . $db->error
        . PHP_EOL
    );
}


/* ============================================================
   KONFIGURASI MQTT
   ============================================================ */

$mqttServer = '127.0.0.1';
$mqttPort = 1883;
$mqttUsername = 'mqttuser';
$mqttPassword = '12345678';

$topic = 'sipk/device1/data';

$clientId = 'PHP_LATENCY_SUB_' . uniqid();

$mqtt = new phpMQTT(
    $mqttServer,
    $mqttPort,
    $clientId
);


/* ============================================================
   FUNGSI WAKTU EPOCH NANODETIK

   Python menggunakan time.time_ns().
   PHP harus menggunakan waktu epoch yang sama.
   ============================================================ */

function epochNanoseconds(): int
{
    $microtime = microtime();

    [$microseconds, $seconds] = explode(
        ' ',
        $microtime
    );

    return (
        ((int) $seconds * 1_000_000_000)
        +
        (int) round(
            (float) $microseconds
            * 1_000_000_000
        )
    );
}


/* ============================================================
   CALLBACK PENERIMAAN PAYLOAD
   ============================================================ */

function processMessage(
    string $topic,
    string $message
): void {

    global $db;

    $receiveTimestampNs = epochNanoseconds();

    $message = trim($message);

    $fields = explode(
        ',',
        $message
    );

    /*
     * Payload pengujian memiliki 17 field:
     *
     * 1-15  = payload utama
     * 16    = message_id
     * 17    = send_timestamp_ns
     */

    if (count($fields) !== 17) {
        echo
            '[INVALID] Jumlah field: '
            . count($fields)
            . PHP_EOL;

        return;
    }

    $deviceId = trim($fields[0]);
    $messageId = trim($fields[15]);
    $sendTimestampNsText = trim($fields[16]);

    if (
        $deviceId === ''
        || $messageId === ''
        || !ctype_digit($sendTimestampNsText)
    ) {
        echo
            '[INVALID] Identitas atau timestamp tidak valid'
            . PHP_EOL;

        return;
    }

    $sendTimestampNs = (int) $sendTimestampNsText;

    $latencyNs = (
        $receiveTimestampNs
        - $sendTimestampNs
    );

    /*
     * Jika hasil negatif, kemungkinan terdapat masalah
     * sinkronisasi waktu atau format timestamp.
     */

    if ($latencyNs < 0) {
        echo
            '[INVALID] Latency negatif: '
            . $messageId
            . PHP_EOL;

        return;
    }

    $latencyMs = (
        $latencyNs / 1_000_000
    );

    $payloadSizeBytes = strlen($message);

    /*
     * RUN_ID berada sebelum tanda "-" pertama.
     *
     * Contoh:
     * 20260618_152010_123456-002-01
     */

    $messageIdParts = explode(
        '-',
        $messageId,
        2
    );

    $runId = $messageIdParts[0];

    $seconds = intdiv(
        $receiveTimestampNs,
        1_000_000_000
    );

    $microseconds = intdiv(
        $receiveTimestampNs
        % 1_000_000_000,
        1_000
    );

    $receivedAt = (
        date(
            'Y-m-d H:i:s',
            $seconds
        )
        .
        sprintf(
            '.%06d',
            $microseconds
        )
    );


    /* ========================================================
       SIMPAN HASIL PENGUJIAN
       INSERT IGNORE mencegah message_id ganda
       ======================================================== */

    $sql = "
        INSERT IGNORE INTO mqtt_test_results (
            run_id,
            message_id,
            device_id,
            send_timestamp_ns,
            receive_timestamp_ns,
            latency_ms,
            payload_size_bytes,
            raw_payload,
            received_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $statement = $db->prepare($sql);

    if (!$statement) {
        echo
            '[DATABASE ERROR] '
            . $db->error
            . PHP_EOL;

        return;
    }

    $statement->bind_param(
        'sssiidiss',
        $runId,
        $messageId,
        $deviceId,
        $sendTimestampNs,
        $receiveTimestampNs,
        $latencyMs,
        $payloadSizeBytes,
        $message,
        $receivedAt
    );

    if (!$statement->execute()) {
        echo
            '[INSERT ERROR] '
            . $statement->error
            . PHP_EOL;

        $statement->close();
        return;
    }

    $inserted = (
        $statement->affected_rows > 0
    );

    $statement->close();

    if ($inserted) {
        echo
            '[RECEIVED] '
            . $messageId
            . ' | Latency: '
            . number_format(
                $latencyMs,
                3
            )
            . ' ms'
            . PHP_EOL;
    } else {
        echo
            '[DUPLICATE] '
            . $messageId
            . PHP_EOL;
    }
}


/* ============================================================
   KONEKSI MQTT
   ============================================================ */

echo 'Menghubungkan subscriber ke broker...' . PHP_EOL;

$connected = $mqtt->connect(
    true,
    null,
    $mqttUsername,
    $mqttPassword
);

if (!$connected) {
    $db->close();

    exit(
        'Gagal terhubung ke broker MQTT.'
        . PHP_EOL
    );
}

echo 'Subscriber MQTT terhubung.' . PHP_EOL;
echo 'Subscribe topik: ' . $topic . PHP_EOL;
echo 'Tekan Ctrl+C untuk berhenti.' . PHP_EOL;


/* ============================================================
   SUBSCRIBE
   ============================================================ */

$topics = [];

$topics[$topic] = [
    'qos' => 0,
    'function' => 'processMessage'
];

$mqtt->subscribe(
    $topics,
    0
);


/* ============================================================
   PROSES SUBSCRIBER
   ============================================================ */

while ($mqtt->proc()) {
    // Menunggu dan memproses pesan MQTT
}


/* ============================================================
   PENUTUP
   ============================================================ */

$mqtt->close();
$db->close();

echo 'Subscriber dihentikan.' . PHP_EOL;

