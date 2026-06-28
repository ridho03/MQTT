<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . "/phpMQTT.php";
use Bluerhinos\phpMQTT;

/******** MQTT ********/
$server = "127.0.0.1";
$port   = 1883;
$client_id = "PHP_SUB_" . rand();

$mqtt = new phpMQTT($server, $port, $client_id);
if(

!$mqtt->connect(

    true,

    NULL,

    "mqttuser",

    "12345678"

)

){

    die(
        "❌ MQTT AUTH FAILED\n"
    );

}

/******** DATABASE ********/
$conn = new mysqli("localhost", "root", "", "carbon2025");
if ($conn->connect_error) {
    die("❌ DB error: " . $conn->connect_error);
}

echo "✅ CONNECTED\n";

$mqtt->subscribe([
    "sipk/device1/data" => [
        "qos" => 0,
        "function" => "insertData"
    ]
], 0);

while ($mqtt->proc()) {}

$mqtt->close();
$conn->close();

/******** HANDLER ********/
function insertData($topic, $msg) {
    global $conn;

    echo "📩 $msg\n";

    $parts = explode(",", $msg);

if(count($parts) < 15){
    echo "❌ FORMAT CSV SALAH\n";
    return;
}

$device_id       = $parts[0];
$humidity        = $parts[1];
$temp_c          = $parts[2];
$temp_f          = $parts[3];
$co_ppm          = $parts[4];
$nh3_ppm         = $parts[5];
$no2_ppm         = $parts[6];
$hydrocarbon_ppm = $parts[7];
$latitude        = $parts[8];
$longitude       = $parts[9];
$speed_kmph = floatval($parts[10]);
$pm_density      = $parts[11];
$flow_sensor = floatval($parts[12]);

$date = $parts[13];
$time = $parts[14];

if ($date == "Invalid" || $time == "Invalid") {
    echo "⚠️ GPS TIME INVALID - SKIP\n";
    return;
}

// 🔥 TAMBAHKAN JUGA INI
$timeParts = explode(":", $time);

if(count($timeParts) < 3){
    echo "⚠️ FORMAT TIME SALAH\n";
    return;
}

// normalisasi format waktu (agar selalu HH:MM:SS)
$timeParts = explode(":", $time);

$hour   = str_pad($timeParts[0], 2, "0", STR_PAD_LEFT);
$minute = str_pad($timeParts[1], 2, "0", STR_PAD_LEFT);
$second = str_pad($timeParts[2], 2, "0", STR_PAD_LEFT);

$time = "$hour:$minute:$second";


$datetime = DateTime::createFromFormat('j/n/Y H:i:s', $date . ' ' . $time);

if ($datetime) {
    $device_timestamp = $datetime->format('Y-m-d H:i:s');
} else {
    echo "⚠️ DEVICE TIME ERROR\n";
    return;
}

$v = floatval($speed_kmph);

// Validasi GPS
if ($latitude == 0 || $longitude == 0) {
    echo "⚠️ GPS INVALID - DATA TIDAK DISIMPAN\n";
    return;
}

if ($v <= 0) {
    $v = 1; // hindari pembagian nol
}

    /******** INSERT SENSOR ********/
    $sqlSensor = "
INSERT INTO sensor_data
(device_id, device_timestamp,
 flow_sensor,
 humidity, temperature_c, temperature_f,
 co_ppm, nh3_ppm, no2_ppm, hydrocarbon_ppm, pm_density,
 created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
";

    $stmtSensor = $conn->prepare($sqlSensor);
    $stmtSensor->bind_param(
"ssddddddddd",
$device_id,
$device_timestamp,
$flow_sensor,
$humidity,
$temp_c,
$temp_f,
$co_ppm,
$nh3_ppm,
$no2_ppm,
$hydrocarbon_ppm,
$pm_density
);

    $stmtSensor->execute();
    $stmtSensor->close();

    /******** INSERT GPS ********/
    if ($latitude !== null && $longitude !== null) {

        $sqlGps = "
INSERT INTO gps_data
(device_id, device_timestamp, latitude, longitude, speed_kmph, created_at)
VALUES (?, ?, ?, ?, ?, NOW())
";

        $stmtGps = $conn->prepare($sqlGps);
        $stmtGps->bind_param(
    "ssddd",
    $device_id,
    $device_timestamp,
    $latitude,
    $longitude,
    $speed_kmph
);

        $stmtGps->execute();
        $stmtGps->close();

        $updateCredit = "
        UPDATE carbon_credits
        SET last_latitude = ?,
            last_longitude = ?,
            last_speed_kmph = ?,
            last_sensor_update = NOW()
        WHERE device_id = ?
        ";

        $stmtUpdate = $conn->prepare($updateCredit);
        $stmtUpdate->bind_param(
            "ddds",
            $latitude,
            $longitude,
            $speed_kmph,
            $device_id
        );
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    /******** HITUNG CO2E ********/

    /******** PARAMETER PERHITUNGAN JURNAL ********/

$Q = 15 + $co_ppm; // flow rate motor (L/min)
$Vm = 22.88; // molar volume
$kh = 1; // humidity correction

$MW_CO  = 28.01;
// $MW_NO2 = 46.01;
// $MW_NH3 = 17.03;

$MC_CO  = ($Q * 60 * $MW_CO)  / ($v * $Vm * 1000000) * $kh;
// $MC_NO2 = ($Q * 60 * $MW_NO2) / ($v * $Vm * 1000000) * $kh;
// $MC_NH3 = ($Q * 60 * $MW_NH3) / ($v * $Vm * 1000000) * $kh;

$co_gkm  = $co_ppm  * $MC_CO;
$no2_gkm = 0;
$nh3_gkm = 0;

/******** FAKTOR GWP ********/

$GWP_CO  = 1.571;
$GWP_NO2 = 0;
$GWP_NH3 = 0;

/******** HITUNG CO2E ********/

$co2e_total =
($co_gkm * $GWP_CO) +
($no2_gkm * $GWP_NO2) +
($nh3_gkm * $GWP_NH3);


    $sqlCo2e = "
INSERT INTO co2e_data
(device_id, device_timestamp,
 co_contribution,
 nh3_contribution,
 no2_contribution,
 co2e_g_km,
 created_at,
 updated_at)
VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
";

$stmtCo2e = $conn->prepare($sqlCo2e);

$stmtCo2e->bind_param(
"ssdddd",
$device_id,
$device_timestamp,
$co_gkm,
$nh3_gkm,
$no2_gkm,
$co2e_total
);

$stmtCo2e->execute();
$stmtCo2e->close();

    /******** UPDATE STATUS ********/
    $updateCredit = "
    UPDATE carbon_credits
    SET last_sensor_update = NOW(),
        sensor_status = 'active'
    WHERE device_id = ?
    ";

    $stmtUpdate = $conn->prepare($updateCredit);
    $stmtUpdate->bind_param("s", $device_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    echo "✅ DATA INSERTED (WITH DEVICE TIMESTAMP)\n";

    /******** KIRIM KE HOSTING ********/
$url = "http://ridho-setiawan.my.id/input.php";

$dataSend = [
    "device_id" => $device_id,
    "device_timestamp" => $device_timestamp,
    "humidity" => $humidity,
    "temp_c" => $temp_c,
    "temp_f" => $temp_f,                 
    "co_ppm" => $co_ppm,
    "nh3_ppm" => $nh3_ppm,
    "no2_ppm" => $no2_ppm,
    "hydrocarbon_ppm" => $hydrocarbon_ppm, 
    "pm_density" => $pm_density,           
    "flow_sensor" => $flow_sensor,         
    "latitude" => $latitude,
    "longitude" => $longitude,
    "speed_kmph" => $speed_kmph,
    "co2e" => $co2e_total
];

$options = [
    "http" => [
        "header"  => "Content-type: application/x-www-form-urlencoded",
        "method"  => "POST",
        "content" => http_build_query($dataSend)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

echo "📤 KIRIM KE HOSTING: $response\n";
}