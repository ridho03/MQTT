<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>CarbonTrade – Pantau Emisi & Kelola Kuota</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#16A34A',      // hijau utama
                        primaryDark: '#065F46',  // hijau gelap
                        surface: '#F9FAFB',
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
            color: #0f172a;

            /* Background: foto + gradasi hijau -> putih */
            background-image:
                linear-gradient(
                    to bottom,
                    rgba(6, 78, 59, 0.70),
                    rgba(6, 78, 59, 0.35),
                    rgba(249, 250, 251, 0.96)
                ),
                url('/images/coba.png'); /* ganti sesuai path gambar */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            background-blend-mode: overlay;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .nav-glass {
            background: linear-gradient(
                to bottom,
                rgba(15, 23, 42, 0.35),
                rgba(15, 23, 42, 0.05)
            );
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        /* Animasi halus untuk hero */
        .fade-up {
            opacity: 0;
            transform: translateY(10px);
            animation: fadeUp 0.8s ease-out forwards;
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="antialiased">

    {{-- NAVBAR --}}
    <header class="w-full sticky top-0 z-20 nav-glass">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
            {{-- Logo --}}
            <div class="flex items-center space-x-2">
    <svg width="36" height="36" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="24" cy="24" r="22" fill="white"/>
  <path d="M16 30c4-10 12-12 16-18c2 12-4 20-16 18z" fill="#16A34A"/>
  <text x="24" y="29" text-anchor="middle" font-size="12" fill="#065F46" font-weight="bold">CO₂</text>
</svg>



    <div>
        <div class="font-semibold text-white text-base sm:text-lg">CarbonTrade</div>
        <div class="text-[11px] text-emerald-100/90">
            Aplikasi Pemantauan Karbon
        </div>
    </div>
</div>


            {{-- Menu --}}
            <nav class="hidden md:flex items-center space-x-8 text-sm">
                <a href="#"
                   class="text-emerald-50/80 hover:text-white transition">
                    Beranda
                </a>
                <div class="h-5 w-px bg-emerald-50/30"></div>
                <a href="{{ route('login') }}"
                   class="text-emerald-50/90 hover:text-white transition">
                    Login
                </a>
                <a href="{{ route('register') }}"
                   class="ml-1 inline-flex items-center px-4 py-2 rounded-full bg-white text-primaryDark text-sm font-semibold shadow hover:bg-softGreen transition">
                    Register
                </a>
            </nav>
        </div>
    </header>

    {{-- KONTEN UTAMA --}}
    <main class="mb-16">
        <section class="max-w-6xl mx-auto px-4 sm:px-6">
            {{-- min-h: tinggi layar dikurangi tinggi navbar kira-kira --}}
            <div class="min-h-[calc(100vh-80px)] flex items-center">
                <div class="grid md:grid-cols-2 gap-8 items-center w-full">

                    {{-- KIRI: TEKS HERO --}}
                    <div class="space-y-5 max-w-xl fade-up">
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight text-white drop-shadow">
                            Pantau Emisi dan Kelola Kuota Kendaraan
                            <span class="block text-softGreen">
                                Dalam Satu Dashboard Sederhana
                            </span>
                        </h1>

                        <p class="text-sm sm:text-base text-slate-800 leading-relaxed glass-card rounded-xl px-4 py-3 shadow-sm">
                            CarbonTrade membantu Anda mencatat kendaraan dan memantau emisi harian secara otomatis
                            menggunakan sensor IoT. Semua data tersaji dalam dashboard yang rapi dan mudah dipahami.
                        </p>

                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center px-5 py-2.5 rounded-full bg-primaryDark text-white text-sm font-semibold shadow hover:bg-primary transition">
                                Mulai Sekarang
                            </a>

                            <a href="{{ route('register') }}"
                               class="inline-flex items-center px-5 py-2.5 rounded-full bg-white text-primaryDark text-sm font-semibold shadow hover:bg-softGreen transition">
                                Daftar Akun
                            </a>
                        </div>

                        <p class="text-[11px] text-emerald-100/90">
                            Dirancang untuk mendukung pengurangan emisi dan pengelolaan kuota karbon kendaraan.
                        </p>
                    </div>

                    {{-- KANAN: KOSONG / RUANG NAPAS, BIAR GAMBAR KELIHATAN --}}
                    <div class="hidden md:block"></div>

                </div>
            </div>
        </section>
    </main>

    {{-- Font Awesome --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" defer></script>
</body>
</html>
