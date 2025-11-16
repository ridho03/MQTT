<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Akun – CarbonTrade</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#16A34A',
                        primaryDark: '#065F46',
                    }
                }
            }
        }
    </script>

    <style>
         body {
            margin: 0;
            min-height: 100vh;
            background-image:
                linear-gradient(
                    to bottom,
                    rgba(6, 78, 59, 0.85),
                    rgba(15, 23, 42, 0.95)
                ),
                url('/images/coba.png'); /* sesuaikan path gambarnya */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }

        .glass-card {
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
    </style>
</head>
<body class="antialiased flex items-center justify-center px-4">

    <div class="w-full max-w-2xl">
        {{-- LOGO + TITLE --}}
        <div class="flex flex-col items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-emerald-500/10 border border-emerald-400/70
                            flex items-center justify-center shadow-lg">
                   <svg width="36" height="36" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="24" cy="24" r="22" fill="white"/>
  <path d="M16 30c4-10 12-12 16-18c2 12-4 20-16 18z" fill="#16A34A"/>
  <text x="24" y="29" text-anchor="middle" font-size="12" fill="#065F46" font-weight="bold">CO₂</text>
</svg>
                </div>
                <div>
                    <div class="font-semibold text-white text-lg">CarbonTrade</div>
                    <div class="text-[11px] text-emerald-100/80">
                        Aplikasi Pemantauan Karbon
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD --}}
        <div class="bg-slate-900/80 border border-slate-700/70 rounded-2xl shadow-2xl
                    px-6 sm:px-8 py-7 backdrop-blur-md">

            <h1 class="text-xl sm:text-2xl font-semibold text-white mb-1">
                Buat akun CarbonTrade
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 mb-6">
                Daftarkan data diri dan kendaraan Anda untuk mulai memantau emisi dan kuota karbon.
            </p>

            {{-- FORM REGISTER --}}
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                {{-- 2 kolom atas --}}
                <div class="grid sm:grid-cols-2 gap-4">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-xs font-medium text-slate-200 mb-1">
                            Name
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                                   text-sm text-slate-50 placeholder-slate-500
                                   px-4 py-3
                                   focus:border-emerald-400 focus:ring-emerald-400"
                            placeholder="Nama lengkap">
                        @error('name')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs font-medium text-slate-200 mb-1">
                            Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                                   text-sm text-slate-50 placeholder-slate-500
                                   px-4 py-3
                                   focus:border-emerald-400 focus:ring-emerald-400"
                            placeholder="nama@email.com">
                        @error('email')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Nomor KK --}}
                <div>
                    <label for="nomor_kartu_keluarga" class="block text-xs font-medium text-slate-200 mb-1">
                        Nomor Kartu Keluarga
                    </label>
                    <input
                        id="nomor_kartu_keluarga"
                        type="text"
                        name="nomor_kartu_keluarga"
                        value="{{ old('nomor_kartu_keluarga') }}"
                        class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                               text-sm text-slate-50 placeholder-slate-500
                               px-4 py-3
                               focus:border-emerald-400 focus:ring-emerald-400"
                        placeholder="16 digit nomor KK">
                    @error('nomor_kartu_keluarga')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NIK --}}
                <div>
                    <label for="nik_e_ktp" class="block text-xs font-medium text-slate-200 mb-1">
                        NIK E-KTP
                    </label>
                    <input
                        id="nik_e_ktp"
                        type="text"
                        name="nik_e_ktp"
                        value="{{ old('nik_e_ktp') }}"
                        class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                               text-sm text-slate-50 placeholder-slate-500
                               px-4 py-3
                               focus:border-emerald-400 focus:ring-emerald-400"
                        placeholder="16 digit NIK">
                    @error('nik_e_ktp')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone_number" class="block text-xs font-medium text-slate-200 mb-1">
                        Phone Number
                    </label>
                    <input
                        id="phone_number"
                        type="text"
                        name="phone_number"
                        value="{{ old('phone_number') }}"
                        class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                               text-sm text-slate-50 placeholder-slate-500
                               px-4 py-3
                               focus:border-emerald-400 focus:ring-emerald-400"
                        placeholder="08xxxxxxxxxx">
                    @error('phone_number')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Address --}}
                <div>
                    <label for="address" class="block text-xs font-medium text-slate-200 mb-1">
                        Address
                    </label>
                    <textarea
                        id="address"
                        name="address"
                        rows="2"
                        class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                               text-sm text-slate-50 placeholder-slate-500
                               px-4 py-3
                               focus:border-emerald-400 focus:ring-emerald-400">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bank info 2 kolom --}}
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="bank_name" class="block text-xs font-medium text-slate-200 mb-1">
                            Bank Name
                        </label>
                        <input
                            id="bank_name"
                            type="text"
                            name="bank_name"
                            value="{{ old('bank_name') }}"
                            class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                                   text-sm text-slate-50 placeholder-slate-500
                                   px-4 py-3
                                   focus:border-emerald-400 focus:ring-emerald-400"
                            placeholder="BCA / BRI / Mandiri">
                        @error('bank_name')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bank_account" class="block text-xs font-medium text-slate-200 mb-1">
                            Bank Account Number
                        </label>
                        <input
                            id="bank_account"
                            type="text"
                            name="bank_account"
                            value="{{ old('bank_account') }}"
                            class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                                   text-sm text-slate-50 placeholder-slate-500
                                   px-4 py-3
                                   focus:border-emerald-400 focus:ring-emerald-400"
                            placeholder="Nomor rekening">
                        @error('bank_account')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Password --}}
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-medium text-slate-200 mb-1">
                            Password
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                                   text-sm text-slate-50 placeholder-slate-500
                                   px-4 py-3
                                   focus:border-emerald-400 focus:ring-emerald-400"
                            placeholder="Minimal 8 karakter">
                        @error('password')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-medium text-slate-200 mb-1">
                            Konfirmasi Password
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            class="block w-full rounded-xl border border-slate-600 bg-slate-900/60
                                   text-sm text-slate-50 placeholder-slate-500
                                   px-4 py-3
                                   focus:border-emerald-400 focus:ring-emerald-400"
                            placeholder="Ulangi password">
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="pt-3 flex items-center justify-between">
                    <a href="{{ route('login') }}"
                       class="text-xs text-slate-300 hover:text-emerald-300">
                        Sudah punya akun? <span class="underline">Masuk</span>
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 rounded-full bg-primaryDark
                                   text-white text-sm font-semibold shadow hover:bg-primary">
                        Daftar
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
