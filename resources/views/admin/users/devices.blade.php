@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">
                Kendaraan & Device – {{ $user->name }}
            </h1>
            <p class="text-sm text-gray-500">
                Email: {{ $user->email }} • Role: {{ ucfirst($user->role) }}
            </p>
        </div>

        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border border-gray-300 text-gray-700 hover:bg-gray-100">
            ← Kembali ke Daftar Pengguna
        </a>
    </div>

    {{-- Kalau belum punya kendaraan --}}
    @if($vehicles->isEmpty())
        <div class="border rounded-lg bg-white p-4 text-sm text-gray-600">
            Pengguna ini belum memiliki kendaraan yang terdaftar.
        </div>
    @else
        <div class="border rounded-xl bg-white overflow-hidden shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Kendaraan</th>
                        <th class="px-4 py-3 text-left">NRKB</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Device ID</th>
                        <th class="px-4 py-3 text-left">Status Device</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicles as $vehicle)
                        <tr class="border-t">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-800">
                                    {{ $vehicle->pemilik_kendaraan ?? 'Kendaraan' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                {{ $vehicle->nrkb ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($vehicle->vehicle_type === 'car')
                                    Mobil
                                @elseif($vehicle->vehicle_type === 'motorcycle')
                                    Motor
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($vehicle->device_id)
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-mono">
                                        {{ $vehicle->device_id }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Belum terdaftar</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($vehicle->device_id)
                                    <span class="inline-flex items-center text-xs text-emerald-600">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500 mr-1"></span>
                                        Aktif / Siap monitoring
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-xs text-orange-500">
                                        <span class="h-2 w-2 rounded-full bg-orange-400 mr-1"></span>
                                        Device belum dipasang
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                @if($vehicle->device_id)
    <a href="{{ route('admin.users.emissions', [$user->id, $vehicle->id]) }}"
       class="px-4 py-2 rounded-lg bg-emerald-500 text-white hover:bg-emerald-600">
        Lihat Data Emisi
    </a>
@endif

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
