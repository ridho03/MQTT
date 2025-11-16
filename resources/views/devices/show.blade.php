@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-4 flex items-center space-x-2">
        <i class="fas fa-microchip"></i>
        <span>Detail Device Sensor</span>
    </h2>

    {{-- Info Kendaraan --}}
    <div class="mb-6 border rounded-lg p-4 bg-gray-50">
        <h3 class="font-semibold mb-2">Informasi Kendaraan</h3>
        <p><strong>NRKB:</strong> {{ $carbonCredit->nrkb }}</p>
        <p><strong>Jenis:</strong>
            @if($carbonCredit->vehicle_type === 'motorcycle')
                Motor
            @elseif($carbonCredit->vehicle_type === 'car')
                Mobil
            @else
                -
            @endif
        </p>
        <p><strong>Pemilik:</strong> {{ $carbonCredit->owner->name ?? '-' }}</p>
    </div>

    {{-- Info Device --}}
    <div class="mb-6 border rounded-lg p-4">
        <h3 class="font-semibold mb-2">Informasi Device</h3>

        @if($carbonCredit->device_id)
            <p><strong>Device ID:</strong> {{ $carbonCredit->device_id }}</p>
            <p><strong>Status Sensor:</strong> {{ $carbonCredit->sensor_status ?? 'inactive' }}</p>
            <p><strong>Threshold Emisi:</strong>
                {{ $carbonCredit->emission_threshold_kg
                    ? number_format($carbonCredit->emission_threshold_kg, 1) . ' kg/hari'
                    : '-' }}
            </p>
            <p><strong>Terdaftar Sejak:</strong>
                {{ $carbonCredit->device_registered_at
                    ? $carbonCredit->device_registered_at->format('d/m/Y H:i')
                    : '-' }}
            </p>
            <p><strong>Catatan:</strong> {{ $carbonCredit->device_notes ?? '-' }}</p>
        @else
            <p class="text-gray-500">
                Kendaraan ini belum memiliki device sensor.
            </p>
            <a href="{{ route('devices.create', $carbonCredit) }}"
               class="inline-flex items-center mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                Daftarkan Device
            </a>
        @endif
    </div>

    {{-- Grafik Emisi Terakhir (opsional) --}}
    @if(isset($co2eLabels) && count($co2eLabels))
        <div class="mb-6 border rounded-lg p-4 bg-gray-50">
            <h3 class="font-semibold mb-3 flex items-center space-x-2">
                <i class="fas fa-chart-line text-primary"></i>
                <span>Grafik Emisi CO₂e Terakhir</span>
            </h3>

            <canvas id="co2eChart" height="120"></canvas>
        </div>
    @endif

    <div class="flex justify-between mt-4">
        <a href="{{ route('devices.index') }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>

        {{-- Kalau nanti mau aktifkan tombol edit, tinggal buka komentar ini --}}
        {{-- @if($carbonCredit->device_id)
            <a href="{{ route('devices.edit', $carbonCredit) }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                <i class="fas fa-edit mr-2"></i> Edit Device
            </a>
        @endif --}}
    </div>
</div>
@endsection
