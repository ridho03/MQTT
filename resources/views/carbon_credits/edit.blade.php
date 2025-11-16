@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-6 text-primary">
        Edit Kendaraan
    </h2>

    {{-- Notifikasi error --}}
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            <strong>Terjadi kesalahan:</strong>
            <ul class="list-disc list-inside mt-2 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('carbon-credits.update', $carbonCredit->id) }}"
        novalidate
    >
        @csrf
        @method('PATCH')

        {{-- ================= DATA PEMILIK ================= --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Data Pemilik</h3>

            {{-- Nomor KK --}}
            <div class="mb-4">
                <label for="nomor_kartu_keluarga" class="block font-semibold mb-1">
                    Nomor Kartu Keluarga
                </label>
                <input
                    type="text"
                    id="nomor_kartu_keluarga"
                    name="nomor_kartu_keluarga"
                    value="{{ old('nomor_kartu_keluarga', $carbonCredit->nomor_kartu_keluarga) }}"
                    class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('nomor_kartu_keluarga') border-red-500 @enderror"
                >
                @error('nomor_kartu_keluarga')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pemilik Kendaraan --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">
                    Status Kepemilikan Kendaraan
                </label>
                <div class="space-y-1">
                    <label class="inline-flex items-center">
                        <input
                            type="radio"
                            name="pemilik_kendaraan"
                            value="milik sendiri"
                            class="mr-2"
                            {{ old('pemilik_kendaraan', $carbonCredit->pemilik_kendaraan) === 'milik sendiri' ? 'checked' : '' }}>
                        <span>Milik sendiri</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input
                            type="radio"
                            name="pemilik_kendaraan"
                            value="milik keluarga satu kk"
                            class="mr-2"
                            {{ old('pemilik_kendaraan', $carbonCredit->pemilik_kendaraan) === 'milik keluarga satu kk' ? 'checked' : '' }}>
                        <span>Milik keluarga satu KK</span>
                    </label>
                </div>
                @error('pemilik_kendaraan')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- NIK e-KTP --}}
            <div class="mb-4">
                <label for="nik_e_ktp" class="block font-semibold mb-1">
                    NIK e-KTP Pemilik
                </label>
                <input
                    type="text"
                    id="nik_e_ktp"
                    name="nik_e_ktp"
                    value="{{ old('nik_e_ktp', $carbonCredit->nik_e_ktp) }}"
                    class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('nik_e_ktp') border-red-500 @enderror"
                >
                @error('nik_e_ktp')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ================= DATA KENDARAAN ================= --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Data Kendaraan</h3>

            {{-- NRKB --}}
            <div class="mb-4">
                <label for="nrkb" class="block font-semibold mb-1">
                    NRKB (Nomor Registrasi Kendaraan Bermotor)
                </label>
                <input
                    type="text"
                    id="nrkb"
                    name="nrkb"
                    value="{{ old('nrkb', $carbonCredit->nrkb) }}"
                    class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('nrkb') border-red-500 @enderror"
                >
                @error('nrkb')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nomor Rangka 5 Digit --}}
            <div class="mb-4">
                <label for="nomor_rangka_5digit" class="block font-semibold mb-1">
                    Nomor Rangka 5 Digit Terakhir
                </label>
                <input
                    type="text"
                    id="nomor_rangka_5digit"
                    name="nomor_rangka_5digit"
                    value="{{ old('nomor_rangka_5digit', $carbonCredit->nomor_rangka_5digit) }}"
                    maxlength="5"
                    class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('nomor_rangka_5digit') border-red-500 @enderror"
                >
                @error('nomor_rangka_5digit')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jenis Kendaraan --}}
            <div class="mb-4">
                <label for="vehicle_type" class="block font-semibold mb-1">
                    Jenis Kendaraan
                </label>
                <select
                    id="vehicle_type"
                    name="vehicle_type"
                    class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary @error('vehicle_type') border-red-500 @enderror"
                >
                    <option value="">-- Pilih Jenis Kendaraan --</option>
                    <option value="car" {{ old('vehicle_type', $carbonCredit->vehicle_type) === 'car' ? 'selected' : '' }}>
                        Mobil
                    </option>
                    <option value="motorcycle" {{ old('vehicle_type', $carbonCredit->vehicle_type) === 'motorcycle' ? 'selected' : '' }}>
                        Motor
                    </option>
                </select>
                @error('vehicle_type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ================= STATUS (KHUSUS ADMIN) ================= --}}
        @if(Auth::user()->role == 'admin')
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3">Status (Admin)</h3>
                <div class="mb-4">
                    <label for="status" class="block font-semibold mb-1">
                        Status Kendaraan
                    </label>
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="pending"   {{ old('status', $carbonCredit->status) === 'pending'   ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="approved"  {{ old('status', $carbonCredit->status) === 'approved'  ? 'selected' : '' }}>Disetujui</option>
                        <option value="available" {{ old('status', $carbonCredit->status) === 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="rejected"  {{ old('status', $carbonCredit->status) === 'rejected'  ? 'selected' : '' }}>Ditolak</option>
                        <option value="sold"      {{ old('status', $carbonCredit->status) === 'sold'      ? 'selected' : '' }}>Terjual</option>
                    </select>
                </div>
            </div>
        @endif

        {{-- ================= TOMBOL AKSI ================= --}}
        <div class="flex justify-between">
            <a href="{{ route('carbon-credits.vehicles') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary">
                <i class="fas fa-arrow-left mr-2" aria-hidden="true"></i> Kembali
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-primary text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600">
                <i class="fas fa-save mr-2" aria-hidden="true"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
