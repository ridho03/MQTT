@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-4 flex items-center space-x-2 text-primary">
        <i class="fas fa-microchip"></i>
        <span>Edit Device Sensor</span>
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
        <p><strong>Device ID:</strong> 
            <span class="font-mono bg-gray-100 px-2 py-1 rounded">
                {{ $carbonCredit->device_id ?? '-' }}
            </span>
        </p>
    </div>

    {{-- Form Edit Device --}}
    <form method="POST" action="{{ route('devices.update', $carbonCredit) }}">
        @csrf
        @method('PATCH')

        {{-- Threshold Emisi --}}
        <div class="mb-4">
            <label for="emission_threshold_kg" class="block font-semibold mb-1">
                Batas Emisi Harian (kg CO₂e) <span class="text-red-600">*</span>
            </label>
            <input type="number"
                   step="0.1"
                   min="0"
                   max="1000"
                   id="emission_threshold_kg"
                   name="emission_threshold_kg"
                   value="{{ old('emission_threshold_kg', $carbonCredit->emission_threshold_kg) }}"
                   class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('emission_threshold_kg') border-red-500 @enderror">
            @error('emission_threshold_kg')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">
                Contoh: 5 kg/hari. Jika emisi harian melebihi nilai ini, sistem bisa memberi peringatan.
            </p>
        </div>

        {{-- Auto Adjustment --}}
        <div class="mb-4">
            <label class="inline-flex items-center space-x-2">
                <input type="checkbox"
                       name="auto_adjustment_enabled"
                       value="1"
                       {{ old('auto_adjustment_enabled', $carbonCredit->auto_adjustment_enabled) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary focus:ring-primary">
                <span class="font-semibold">Aktifkan penyesuaian otomatis kuota berdasarkan emisi</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">
                Jika diaktifkan, sistem dapat menyesuaikan kuota karbon atau memberikan rekomendasi
                berdasarkan data emisi real-time.
            </p>
        </div>

        {{-- Catatan --}}
        <div class="mb-4">
            <label for="notes" class="block font-semibold mb-1">
                Catatan Device (opsional)
            </label>
            <textarea id="notes"
                      name="notes"
                      rows="3"
                      class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('notes') border-red-500 @enderror">{{ old('notes', $carbonCredit->device_notes) }}</textarea>
            @error('notes')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-between mt-6">
            <a href="{{ route('devices.show', $carbonCredit) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Batal
            </a>

            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-primary text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
