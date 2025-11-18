<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CarbonCredit;
use App\Models\Co2eData;
use App\Models\SensorData;
use Illuminate\Http\Request;


class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount([
            'vehicles as vehicles_count',               // kendaraan total
            'vehicles as devices_count' => function ($q) {
                $q->whereNotNull('device_id');         // yang sudah punya device
            },
        ])->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    // ➜ halaman detail kendaraan & device milik seorang user
    public function devices(User $user)
    {
        // relasi vehicles sudah kamu buat di model User
        $vehicles = $user->vehicles()
            ->with('owner')      // kalau di CarbonCredit ada relasi owner()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users.devices', [
            'user'     => $user,
            'vehicles' => $vehicles,
        ]);
    }

    public function showEmissions($userId, $carbonCreditId)
{
    $user = User::findOrFail($userId);
    $carbonCredit = CarbonCredit::findOrFail($carbonCreditId);

    // Ambil device_id
    $deviceId = $carbonCredit->device_id;

    // Ambil data mentah
    $rawData = SensorData::where('device_id', $deviceId)
        ->orderBy('timestamp', 'asc')
        ->get(['timestamp', 'co_ppm', 'nh3_ppm', 'no2_ppm']);

    // Ambil data CO₂e
    $co2eData = Co2eData::where('device_id', $deviceId)
        ->orderBy('timestamp', 'asc')
        ->get(['timestamp', 'co2e_mg_m3']);

    return view('admin.users.emissions', compact(
        'user', 'carbonCredit', 'rawData', 'co2eData'
    ));
}

}