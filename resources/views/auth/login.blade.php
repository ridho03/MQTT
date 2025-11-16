<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Login – CarbonTrade</title>
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
                        softGreen: '#DCFCE7',
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
<body class="antialiased text-slate-50">

    {{-- Wrapper full page --}}
    <div class="min-h-screen flex flex-col items-center justify-center px-4">

        {{-- Logo + Judul di atas card --}}
        <div class="mb-6 text-center">
            <div class="inline-flex items-center space-x-2">
                <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center shadow-md">
                    <svg width="36" height="36" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="24" cy="24" r="22" fill="white"/>
  <path d="M16 30c4-10 12-12 16-18c2 12-4 20-16 18z" fill="#16A34A"/>
  <text x="24" y="29" text-anchor="middle" font-size="12" fill="#065F46" font-weight="bold">CO₂</text>
</svg>
                </div>
                <div class="text-left">
                    <p class="text-white font-semibold text-lg leading-tight">CarbonTrade</p>
                    <p class="text-emerald-100/90 text-[11px]">
                        Aplikasi Pemantauan Karbon
                    </p>
                </div>
            </div>
        </div>

        {{-- Card Login --}}
        <div class="w-full max-w-md glass-card rounded-2xl shadow-2xl px-7 py-6 space-y-5">

            {{-- Judul section --}}
            <div class="text-center mb-1">
                <h1 class="text-xl font-semibold text-white">
                    Masuk ke akun Anda
                </h1>
                <p class="text-xs text-slate-300 mt-1">
                    Pantau emisi & kelola kuota kendaraan dari satu dashboard.
                </p>
            </div>

            {{-- Pesan status (jika ada) --}}
            @if (session('status'))
                <div class="mb-3 text-sm text-emerald-200 bg-emerald-900/40 border border-emerald-700 px-3 py-2 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            {{-- FORM LOGIN --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-200">
                        Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="mt-1 block w-full rounded-xl border border-slate-600 bg-slate-900/60
                               text-sm text-slate-50 placeholder-slate-400
                               px-4 py-3
                               focus:border-emerald-400 focus:ring-emerald-400"
                        placeholder="nama@email.com"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-medium text-slate-200">
                            Password
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-[11px] text-emerald-200 hover:text-emerald-100">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="mt-1 block w-full rounded-xl border border-slate-600 bg-slate-900/60
                               text-sm text-slate-50 placeholder-slate-400
                               px-4 py-3
                               focus:border-emerald-400 focus:ring-emerald-400"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between mt-1">
                    <label class="inline-flex items-center space-x-2 text-xs text-slate-200">
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                            class="rounded border-slate-500 bg-slate-900 text-emerald-500 focus:ring-emerald-500"
                        >
                        <span>Ingat saya</span>
                    </label>
                </div>

                {{-- Tombol Login --}}
                <div class="pt-1">
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-4 py-2.5
                                   rounded-xl bg-primaryDark hover:bg-primary text-sm font-semibold
                                   text-white shadow-md transition">
                        Masuk
                    </button>
                </div>
            </form>

            {{-- Link ke register --}}
            <div class="text-center pt-2 border-t border-slate-700/70 mt-2">
                <p class="text-[11px] text-slate-300">
                    Belum punya akun?
                    <a href="{{ route('register') }}"
                       class="text-emerald-200 font-semibold hover:text-emerald-100">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        </div>

        {{-- Catatan kecil di bawah --}}
        <!-- <div class="mt-4 text-[10px] text-slate-300/80">
            Prototype untuk kebutuhan akademik & riset.
        </div> -->
    </div>

</body>
</html>
