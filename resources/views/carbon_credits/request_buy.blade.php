@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-6 text-primary">Pengajuan Pembelian Kuota Karbon</h2>

    <div class="mb-6 p-4 bg-green-100 rounded text-green-800">
        <p><strong>NRKB:</strong> {{ $carbonCredit->nrkb }}</p>
        <p><strong>Kuota Saat Ini:</strong> {{ number_format($carbonCredit->effective_quota, 2) }} kg CO₂e</p>
    </div>

    <form method="POST"
          action="{{ route('carbon-credits.submit-buy-request', $carbonCredit->id) }}">
        @csrf

        <div class="mb-4">
            <label for="quantity_to_buy" class="block font-semibold mb-1">
                Jumlah Kuota yang Ingin Dibeli (kg CO₂e) <span class="text-red-600">*</span>
            </label>
            <input type="number"
                   id="quantity_to_buy"
                   name="quantity_to_buy"
                   value="{{ old('quantity_to_buy') }}"
                   step="0.01"
                   min="0.01"
                   class="w-full rounded border border-gray-300 px-3 py-2"
                   required>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('carbon-credits.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                <i class="fas fa-paper-plane mr-2"></i> Kirim Pengajuan
            </button>
        </div>
    </form>
</div>
@endsection
