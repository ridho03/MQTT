<?php

namespace App\Http\Controllers;

use App\Models\CarbonCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    /**
     * List device / kendaraan
     */
    
    public function index(Request $request)
{
    $user = Auth::user();

    if ($user->role === 'admin') {
        // Admin bisa lihat semua / filter per pemilik
        $query = CarbonCredit::with('owner');

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        $carbonCredits = $query->get();
    } else {
        // User biasa: hanya kendaraan miliknya sendiri
        $carbonCredits = CarbonCredit::with('owner')
            ->where('owner_id', $user->id)
            ->get();
    }

    return view('devices.index', compact('carbonCredits'));
}

    /**
     * FORM daftar device untuk 1 kendaraan
     */
    public function create(CarbonCredit $carbonCredit)
    {
        // Hanya admin atau pemilik kendaraan yang boleh
        if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // PENTING: kirim $carbonCredit ke view
        return view('devices.create', [
            'carbonCredit' => $carbonCredit,
        ]);
    }

    /**
     * Simpan device baru untuk kendaraan
     */
    public function store(Request $request, CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'device_id'             => 'required|string|unique:carbon_credits,device_id|max:50',
            'emission_threshold_kg' => 'required|numeric|min:0|max:1000',
            'notes'                 => 'nullable|string|max:500',
        ]);

        $carbonCredit->update([
            'device_id'              => $validated['device_id'],
            'emission_threshold_kg'  => $validated['emission_threshold_kg'],
            'sensor_status'          => 'inactive',
            'auto_adjustment_enabled'=> true,
            'device_registered_at'   => now(),
            'device_notes'           => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('devices.index')
            ->with('success', 'Device berhasil didaftarkan untuk kendaraan ' . $carbonCredit->nrkb);
    }

    /**
     * DETAIL device
     */
    public function show(CarbonCredit $carbonCredit)
{
    if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    if (!$carbonCredit->device_id) {
        return redirect()->route('devices.index')
            ->with('error', 'Kendaraan ini belum memiliki device sensor.');
    }

    $recentSensorData = $carbonCredit->sensorData()->latest('timestamp')->limit(10)->get();
    $recentCo2eData   = $carbonCredit->co2eData()->latest('timestamp')->limit(10)->get();
    $recentGpsData    = $carbonCredit->gpsData()->latest('timestamp')->limit(10)->get();

    return view('devices.show', compact(
        'carbonCredit',
        'recentSensorData',
        'recentCo2eData',
        'recentGpsData'
    ));
}


    /**
     * FORM edit device
     */
    public function edit(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$carbonCredit->device_id) {
            return redirect()->route('devices.index')
                ->with('error', 'Kendaraan ini belum memiliki device sensor.');
        }

        return view('devices.edit', [
            'carbonCredit' => $carbonCredit,
        ]);
    }

    /**
     * UPDATE device
     */
    public function update(Request $request, CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'emission_threshold_kg'   => 'required|numeric|min:0|max:1000',
            'auto_adjustment_enabled' => 'nullable|boolean',
            'notes'                   => 'nullable|string|max:500',
        ]);

        $carbonCredit->update([
            'emission_threshold_kg'   => $validated['emission_threshold_kg'],
            'auto_adjustment_enabled' => $validated['auto_adjustment_enabled'] ?? false,
            'device_notes'            => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('devices.index', $carbonCredit)
            ->with('success', 'Pengaturan device berhasil diupdate.');
    }

    /**
     * HAPUS device dari kendaraan (unregister)
     */
    public function destroy(CarbonCredit $carbonCredit)
{
    // Hanya admin atau pemilik kendaraan yang boleh melepas device
    if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
        abort(403, 'Anda tidak berhak menghapus device pada kendaraan ini.');
    }

    $carbonCredit->update([
        'device_id'               => null,
        'sensor_status'           => 'inactive',   // <- JANGAN null, isi 'inactive'
        'auto_adjustment_enabled' => false,

        // kolom numeric yang NOT NULL, isi 0
        'current_co2e_mg_m3'      => 0,
        'daily_emissions_kg'      => 0,
        'emission_threshold_kg'   => 0,

        // kolom yang memang boleh null
        'last_sensor_update'      => null,
        'last_latitude'           => null,
        'last_longitude'          => null,
        'last_speed_kmph'         => null,
        'device_registered_at'    => null,
        'device_notes'            => null,
    ]);

    return redirect()
        ->route('devices.index')
        ->with('success', 'Device berhasil dilepas dari kendaraan ' . $carbonCredit->nrkb);
}



    /**
     * QR Code data
     */
    public function generateQrCode(CarbonCredit $carbonCredit)
    {
        if (!$carbonCredit->device_id) {
            return response()->json(['error' => 'Device not registered'], 400);
        }

        $setupData = [
            'device_id'     => $carbonCredit->device_id,
            'vehicle_nrkb'  => $carbonCredit->nrkb,
            'vehicle_type'  => $carbonCredit->vehicle_type,
            'api_endpoint'  => url('/api/mqtt'),
            'mqtt_topics'   => [
                'sensor_data' => 'sensors/emission/data',
                'co2e_data'   => 'sensors/emission/co2e',
                'gps_data'    => 'sensors/gps/location',
                'status'      => 'sensors/emission/status',
            ],
        ];

        return response()->json([
            'qr_data'   => base64_encode(json_encode($setupData)),
            'setup_url' => route('devices.setup', $carbonCredit->device_id),
        ]);
    }

    /**
     * Halaman setup untuk teknisi (scan QR)
     */
    public function setup($deviceId)
    {
        $carbonCredit = CarbonCredit::where('device_id', $deviceId)->first();

        if (!$carbonCredit) {
            abort(404, 'Device not found');
        }

        $setupInstructions = [
            'device_id'    => $deviceId,
            'mqtt_broker'  => 'test.mosquitto.org',
            'mqtt_port'    => 1883,
            'api_endpoint' => url('/api/mqtt'),
            'topics'       => [
                'sensors/emission/data',
                'sensors/emission/co2e',
                'sensors/gps/location',
                'sensors/emission/status',
            ],
        ];

        return view('devices.setup', compact('carbonCredit', 'setupInstructions'));
    }
}
