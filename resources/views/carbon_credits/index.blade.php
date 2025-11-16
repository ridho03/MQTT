@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold">
            @if(Auth::user()->isAdmin())
                Semua Kuota Karbon
            @else
                Kuota Karbon Saya
            @endif
        </h2>
    </div>

    @if($carbonCredits->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 rounded-lg border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold uppercase">No</th>

                        @if(Auth::user()->isAdmin())
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Pemilik</th>
                        @endif

                        <th class="px-4 py-3 text-left text-sm font-semibold uppercase">NRKB Kendaraan</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Emisi Harian (kg)</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Kuota Karbon (kg CO₂e)</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Harga/Unit</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach($carbonCredits as $index => $credit)
                        <tr class="hover:bg-gray-50">

                            {{-- NO --}}
                            <td class="px-4 py-3 text-sm">
                                {{ $carbonCredits->firstItem() + $index }}
                            </td>

                            {{-- PEMILIK (ADMIN SAJA) --}}
                            @if(Auth::user()->isAdmin())
                                <td class="px-4 py-3 text-sm">
                                    {{ $credit->owner->name ?? '-' }}
                                </td>
                            @endif

                            {{-- NRKB --}}
                            <td class="px-4 py-3 text-sm">
                                {{ $credit->nrkb ?? '-' }}
                            </td>

                            {{-- EMISI HARIAN --}}
                            <td class="px-4 py-3 text-sm">
                                {{ number_format($credit->daily_emissions_kg ?? 0, 2) }} kg
                            </td>

                            {{-- KUOTA --}}
                            <td class="px-4 py-3 text-sm">
                                {{ number_format($credit->effective_quota, 2) }}
                                <span class="text-xs text-gray-500">kg CO₂e</span>
                            </td>

                            {{-- HARGA --}}
                            <td class="px-4 py-3 text-sm">
                                Rp {{ number_format($credit->price_per_unit, 0, ',', '.') }}
                            </td>

                            {{-- STATUS --}}
                            <td class="px-4 py-3 text-sm">
                                @switch($credit->status)
                                    @case('pending')
                                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">
                                            Menunggu Persetujuan
                                        </span>
                                        @break
                                    @case('approved')
                                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                            Disetujui
                                        </span>
                                        @break
                                    @case('pending_sale')
                                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">
                                            Menunggu Persetujuan Penjualan
                                        </span>
                                        @break
                                    @case('pending_buy')
                                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">
                                            Menunggu Persetujuan Pembelian
                                        </span>
                                        @break
                                    @case('available')
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">
                                            Tersedia
                                        </span>
                                        @break
                                    @case('rejected')
                                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">
                                            Ditolak
                                        </span>
                                        @break
                                    @case('sold')
                                        <span class="px-3 py-1 rounded-full bg-gray-200 text-gray-600 text-xs font-semibold">
                                            Terjual
                                        </span>
                                        @break
                                @endswitch
                            </td>

                            {{-- AKSI --}}
                            <td class="px-4 py-3 text-sm">
    <div class="flex space-x-2">

        {{-- EDIT: admin atau pemilik --}}
        @if((!Auth::user()->isAdmin() && $credit->owner_id === Auth::id()) || Auth::user()->isAdmin())
            <!-- <a href="{{ route('carbon-credits.edit', $credit->id) }}"
               class="px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500"
               title="Edit">
                <i class="fas fa-edit"></i>
            </a> -->
        @endif

        {{-- ========== BAGIAN USER ========== --}}
        @if(!Auth::user()->isAdmin() && $credit->owner_id === Auth::id())

            {{-- JUAL --}}
            @if($credit->status === 'pending_sale')
                {{-- sudah diajukan, non-aktif --}}
                <button type="button"
                        class="px-2 py-1 bg-gray-300 text-gray-600 rounded cursor-not-allowed text-xs flex items-center"
                        title="Permintaan penjualan sedang menunggu persetujuan admin">
                    <i class="fas fa-store"></i>
                    <span class="ml-1">Jual</span>
                </button>
            @else
                {{-- bisa ajukan penjualan --}}
                <a href="{{ route('carbon-credits.request-sale', $credit->id) }}"
   class="px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs flex items-center"
   title="Ajukan Penjualan">
    <i class="fas fa-store"></i>
    <span class="ml-1">Jual</span>
</a>

            @endif

            {{-- BELI --}}
            {{-- BELI: pemilik minta tambahan kuota --}}
@if(!Auth::user()->isAdmin() && $credit->owner_id === Auth::id() && in_array($credit->status, ['approved', 'available']))
    <a href="{{ route('carbon-credits.request-buy', $credit->id) }}"
       class="px-2 py-1 bg-purple-600 text-white rounded hover:bg-purple-700 text-xs flex items-center"
       title="Ajukan Pembelian Kuota">
        <i class="fas fa-shopping-cart"></i>
        <span class="ml-1">Beli</span>
    </a>
@endif


        @endif
        {{-- ========== END BAGIAN USER ========== --}}

       {{-- ========== BAGIAN ADMIN: APPROVE / REJECT ========== --}}
@if(Auth::user()->isAdmin())

    {{-- 1. ADMIN: approve / reject KUOTA BARU --}}
    @if($credit->status === 'pending')
        <form action="{{ route('carbon-credits.approve', $credit->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button class="px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                    onclick="return confirm('Setujui kuota ini?')"
                    title="Setujui Kuota">
                <i class="fas fa-check"></i>
            </button>
        </form>

        <form action="{{ route('carbon-credits.reject', $credit->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                    onclick="return confirm('Tolak kuota ini?')"
                    title="Tolak Kuota">
                <i class="fas fa-times"></i>
            </button>
        </form>
    @endif

    {{-- ========== BAGIAN ADMIN: APPROVE / REJECT ========== --}}
@if(Auth::user()->isAdmin())

    {{-- 1. ADMIN: approve / reject KUOTA BARU --}}
    @if($credit->status === 'pending')
        <form action="{{ route('carbon-credits.approve', $credit->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button class="px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                    onclick="return confirm('Setujui kuota ini?')"
                    title="Setujui Kuota">
                <i class="fas fa-check"></i>
            </button>
        </form>

        <form action="{{ route('carbon-credits.reject', $credit->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                    onclick="return confirm('Tolak kuota ini?')"
                    title="Tolak Kuota">
                <i class="fas fa-times"></i>
            </button>
        </form>
    @endif

    {{-- 2. ADMIN: approve / reject PERMINTAAN PENJUALAN --}}
    @if($credit->status === 'pending_sale')
        <form action="{{ route('carbon-credits.approve-sale-request', $credit->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button class="px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                    onclick="return confirm('Setujui penjualan kuota ini?')"
                    title="Setujui Penjualan">
                <i class="fas fa-check"></i>
            </button>
        </form>

        <form action="{{ route('carbon-credits.reject-sale-request', $credit->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                    onclick="return confirm('Tolak penjualan kuota ini?')"
                    title="Tolak Penjualan">
                <i class="fas fa-times"></i>
            </button>
        </form>
    @endif

    {{-- 3. ADMIN: approve / reject PERMINTAAN PEMBELIAN --}}
    @if($credit->status === 'pending_buy')
        <form action="{{ route('carbon-credits.approve-buy-request', $credit->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button class="px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                    onclick="return confirm('Setujui permintaan pembelian kuota ini?')"
                    title="Setujui Pembelian">
                <i class="fas fa-check"></i>
            </button>
        </form>

        <form action="{{ route('carbon-credits.reject-buy-request', $credit->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                    onclick="return confirm('Tolak permintaan pembelian kuota ini?')"
                    title="Tolak Pembelian">
                <i class="fas fa-times"></i>
            </button>
        </form>
    @endif

@endif
{{-- ========== END ADMIN ========== --}}


            {{-- kalau nanti ada pending_buy, bisa ditambah di sini --}}
        @endif
        {{-- ========== END ADMIN ========== --}}
    </div>
</td>


                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-4">
            {{ $carbonCredits->links() }}
        </div>
    @else
        <div class="text-center py-10">
            <i class="fas fa-leaf fa-3x text-gray-400 mb-4"></i>
            <h3 class="text-gray-600 text-lg mb-2">Belum ada kuota karbon</h3>
            <p class="text-gray-500 mb-4">
                Kamu belum memiliki kuota karbon untuk dijual atau dibeli.
            </p>
        </div>
    @endif
</div>
@endsection
