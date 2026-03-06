<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

public function exportCSV($device)
{

    $filename = "report_emisi_" . $device . ".csv";

    $data = DB::table('sensor_data')
    ->join('co2e_data', function($join) {
        $join->on('sensor_data.device_id','=','co2e_data.device_id')
             ->on('sensor_data.device_timestamp','=','co2e_data.device_timestamp');
    })
    ->where('sensor_data.device_id', $device)
    ->orderBy('sensor_data.device_timestamp','desc')
    ->select(
        'sensor_data.device_id',
        'sensor_data.device_timestamp',
        'sensor_data.humidity',
        'sensor_data.temperature_c',
        'sensor_data.temperature_f',
        'sensor_data.co_ppm',
        'sensor_data.nh3_ppm',
        'sensor_data.no2_ppm',
        'sensor_data.hydrocarbon_ppm',
        'sensor_data.pm_density',
        'co2e_data.co_contribution',
        'co2e_data.co_mg_m3',
        'co2e_data.co2e_mg_m3'
    )
    ->get();

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate",
    ];

    $callback = function() use ($data){

    $file = fopen('php://output','w');

    $columns = [
        'Device ID',
        'Timestamp',
        'Humidity',
        'Temp C',
        'Temp F',
        'CO ppm',
        'NH3 ppm',
        'NO2 ppm',
        'HC ppm',
        'PM Density',
        'CO Contribution',
        'CO mg/m3',
        'CO2e mg/m3'
    ];

    // tulis header csv
    fputcsv($file, $columns);

    foreach($data as $row){

        fputcsv($file, [
            $row->device_id,
            $row->device_timestamp,
            $row->humidity,
            $row->temperature_c,
            $row->temperature_f,
            $row->co_ppm,
            $row->nh3_ppm,
            $row->no2_ppm,
            $row->hydrocarbon_ppm,
            $row->pm_density,
            $row->co_contribution,
            $row->co_mg_m3,
            $row->co2e_mg_m3
        ]);

    }

    fclose($file);
};

    return response()->stream($callback,200,$headers);

}

}