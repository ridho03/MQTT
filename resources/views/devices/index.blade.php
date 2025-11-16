@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold flex items-center space-x-3 text-primary">
            <i class="fas fa-microchip"></i>
            <span>Manajemen Device Sensor</span>
        </h1>
        <div class="text-gray-600">
            <i class="fas fa-info-circle"></i>
            Daftarkan device sensor untuk monitoring emisi real-time
        </div>
    </div>

    <!-- Status Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        @php
            $totalVehicles = $carbonCredits->count();
            $withDevice = $carbonCredits->where('device_id', '!=', null)->count();
            $activeDevices = $carbonCredits->where('sensor_status', 'active')->count();
            $withoutDevice = $totalVehicles - $withDevice;
        @endphp
        
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Kendaraan</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalVehicles }}</p>
                </div>
                <i class="fas fa-car text-blue-500 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Dengan Device</p>
                    <p class="text-2xl font-bold text-green-600">{{ $withDevice }}</p>
                </div>
                <i class="fas fa-microchip text-green-500 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Device Aktif</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $activeDevices }}</p>
                </div>
                <i class="fas fa-wifi text-blue-500 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Belum Ada Device</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $withoutDevice }}</p>
                </div>
                <i class="fas fa-exclamation-triangle text-orange-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Vehicle List -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Kendaraan</h3>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Aktif</span>
                </div>
                {{-- Legend Terdaftar dihapus --}}
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                    <span class="text-sm text-gray-600">Belum Ada Device</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kendaraan
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pemilik
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Device ID
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Threshold
                        </th>
                        {{-- Kolom "Terdaftar" dihapus --}}
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($carbonCredits as $credit)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center space-x-3">
                                <i class="fas {{ $credit->vehicle_type === 'motorcycle' ? 'fa-motorcycle' : 'fa-car' }} text-blue-500"></i>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $credit->nrkb }}</p>
                                    <p class="text-sm text-gray-600">{{ ucfirst($credit->vehicle_type) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div>
                                <p class="font-medium text-gray-800">{{ $credit->owner->name }}</p>
                                <p class="text-sm text-gray-600">{{ $credit->owner->email }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($credit->device_id)
                                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                    {{ $credit->device_id }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">Belum terdaftar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($credit->device_id)
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full {{ 
                                        $credit->sensor_status === 'active' ? 'bg-green-500' : 
                                        ($credit->sensor_status === 'error' ? 'bg-red-500' : 
                                        ($credit->sensor_status === 'idle' ? 'bg-yellow-500' : 'bg-gray-400')) 
                                    }}"></div>
                                    <span class="text-sm capitalize {{ 
                                        $credit->sensor_status === 'active' ? 'text-green-600' : 
                                        ($credit->sensor_status === 'error' ? 'text-red-600' : 
                                        ($credit->sensor_status === 'idle' ? 'text-yellow-600' : 'text-gray-600')) 
                                    }}">
                                        {{ $credit->sensor_status ?? 'inactive' }}
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $credit->emission_threshold_kg ? number_format($credit->emission_threshold_kg, 1) . ' kg/hari' : '-' }}
                        </td>

                        {{-- Kolom Terdaftar DIHAPUS --}}

                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center space-x-2">

                                @if($credit->device_id)
                                    {{-- DETAIL --}}
                                    <a href="{{ route('devices.show', $credit) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Detail
                                    </a>

                                    <span class="text-gray-300">|</span>

                                    {{-- EDIT --}}
                                    <a href="{{ route('devices.edit', $credit) }}" 
                                       class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        Edit
                                    </a>

                                    <span class="text-gray-300">|</span>

                                    {{-- HAPUS --}}
                                    <form action="{{ route('devices.destroy', $credit) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Yakin menghapus device ini?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Hapus
                                        </button>
                                    </form>

                                @else
                                    {{-- Device belum terdaftar --}}
                                    <a href="{{ route('devices.create', $credit) }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium">
                                        Daftarkan Device
                                    </a>
                                @endif

                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            <i class="fas fa-car text-4xl text-gray-300 mb-2"></i>
                            <p>Belum ada kendaraan terdaftar</p>
                            <a href="{{ route('carbon-credits.create') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                Daftarkan kendaraan pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- QR modal & script tetap, karena sekarang tombol QR sudah tidak dipakai, ini opsional kalau nanti mau dipakai lagi --}}
@endsection
