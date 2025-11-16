@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Daftar Pengguna</h1>
            <p class="text-sm text-gray-500">Semua pengguna yang terdaftar di sistem.</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Nama</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Role</th>
                    <th class="px-4 py-2 text-center">Kendaraan</th>
                    <th class="px-4 py-2 text-center">Dengan Device</th>
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-t hover:bg-gray-50/70">
                        <td class="px-4 py-2">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->email }}</td>
                        <td class="px-4 py-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs
                                {{ $user->role === 'admin' ? 'bg-yellow-100 text-yellow-800' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ ucfirst($user->role ?? 'user') }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            {{ $user->carbon_credits_count ?? 0 }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            {{ $user->devices_count ?? 0 }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('devices.index', ['owner_id' => $user->id]) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                                Lihat Kendaraan & Device
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">
                            Belum ada pengguna yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
