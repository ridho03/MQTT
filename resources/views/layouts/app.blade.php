<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'Carbon Marketplace') }}</title>
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class', // ✅ penting untuk dark mode berbasis class
        theme: {
            extend: {
                colors: {
                    primary: '#10B981',
                    secondary: '#3B82F6',
                    dark: '#0F172A',
                    light: '#F9FAFB',
                }
            }
        }
    }
</script>

<script>
    // Inisialisasi dark mode dari localStorage
    (function () {
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    })();

    function toggleTheme() {
        const html = document.documentElement;
        const isDark = html.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }
</script>


    <style>
        .dark body,
    .dark {
        color: #e2e8f0 !important; /* slate-200 */
    }

    .dark .text-gray-700,
    .dark .text-gray-800,
    .dark .text-gray-900 {
        color: #e2e8f0 !important;
    }

    .dark .bg-white,
    .dark .bg-gray-50,
    .dark .bg-light {
        background-color: #1e293b !important; /* slate-800 */
    }

    .dark .border-gray-200,
    .dark .border-gray-300 {
        border-color: #334155 !important; /* slate-600 */
    }

    .dark input,
    .dark select,
    .dark textarea {
        background-color: #0f172a !important; /* slate-900 */
        color: #f1f5f9 !important; /* slate-100 */
        border-color: #334155 !important;
    }

    .dark .sidebar-item {
        color: #e2e8f0 !important;
    }
        /* .chart-container {
            position: relative;
            height: 300px;
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                left: -100%;
                z-index: 50;
            }
            .sidebar.active {
                left: 0;
            }
        } */
    </style>
</head>
<body
    class="antialiased bg-gray-100 text-gray-900 dark:bg-slate-900 dark:text-slate-100 transition-colors duration-300"
    x-data
>

    
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="sidebar bg-white w-64 border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-leaf text-primary text-2xl"></i>
                    <h1 class="text-xl font-bold text-dark">CarbonTrade</h1>
                </div>
                
            </div>

            <nav class="flex-1 p-4 space-y-2">
    <a href="{{ url('/dashboard') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->is('dashboard') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">
        <i class="fas fa-chart-line"></i>
        <span>Dashboard</span>
    </a>

    @auth
        <a href="{{ route('transactions.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('transactions.*') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fas fa-exchange-alt"></i>
            <span>Transaksi</span>
        </a>

        <a href="{{ route('carbon-credits.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ (request()->routeIs('carbon-credits.*') && !request()->routeIs('carbon-credits.vehicles')) ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fas fa-coins"></i>
            <span>Karbon Saya</span>
        </a>

        <a href="{{ route('carbon-credits.vehicles') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('carbon-credits.vehicles') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fas fa-car-side"></i>
            <span>Kendaraan</span>
        </a>

        <a href="{{ route('emission.monitoring') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('emission.monitoring') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fas fa-chart-line mr-1"></i>
            <span>Monitoring Emisi</span>
        </a>

        @if(Auth::user()->isAdmin())
    
        <a href="{{ route('devices.index') }}"
           class="flex items-center px-4 py-2 rounded-lg 
                  {{ request()->routeIs('devices.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <i class="fa-solid fa-microchip w-5 mr-3"></i>
            <span>Device Sensor</span>
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="flex items-center px-4 py-2 text-sm
                  {{ request()->routeIs('admin.users.index') ? 'bg-green-100 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
            <i class="fas fa-users mr-2"></i>
            <span>Daftar Pengguna</span>
        </a>

    
@endif

        <!-- <a href="{{ route('payouts.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('payouts.*') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fas fa-money-bill-wave"></i>
            <span>Pencairan</span>
        </a> -->
    @endauth
</nav>


            @auth
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center space-x-3 mb-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}" alt="User" class="w-10 h-10 rounded-full" />
                    <div>
                        <p class="font-medium text-dark">{{ Auth::user()->name }}</p>
                        <p class="text-sm text-gray-500">{{ Auth::user()->isAdmin() ? 'Admin' : 'User' }}</p>
                    </div>
                </div>

                {{-- Logout button --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center space-x-2 px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
            @endauth
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white border-b border-gray-200 p-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    
                    <button id="sidebarToggle" class="md:hidden text-gray-600" aria-label="Toggle sidebar">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    @php
                    $routeName = request()->route() ? request()->route()->getName() : '';
                    $pageTitles = [
                        'dashboard' => 'Dashboard',
                        'transactions.index' => 'Transaksi',
                        'carbon-credits.index' => 'Kuota Karbon',
                        'carbon-credits.vehicles' => 'Kelola Kendaraan',
                        'payouts.index' => 'Pencairan',
                        'emission.monitoring' => 'Monitoring Emisi',
                        'devices.index' => 'Device Sensor',
                        ];
                        $defaultTitle = 'Dashboard';
                        $title = $pageTitles[$routeName] ?? $defaultTitle;
                    @endphp

                    <h2 class="text-xl font-semibold text-dark">
                        @hasSection('header')
                            @yield('header')
                        @else
                            {{ $title }}
                        @endif
                    </h2>
                </div>
                <div class="flex items-center space-x-3">
            {{-- Toggle Dark / Light --}}
            <button
                type="button"
                onclick="toggleTheme()"
                class="inline-flex items-center px-3 py-1.5 rounded-full border border-gray-300/70 dark:border-slate-600
                       bg-white/90 dark:bg-slate-800 text-xs font-medium text-gray-700 dark:text-slate-100
                       shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                <span class="mr-2">
                    <span class="hidden dark:inline">🌙 Dark</span>
                    <span class="dark:hidden">☀️ Light</span>
                </span>
                <span class="w-7 h-4 flex items-center bg-gray-200 dark:bg-slate-600 rounded-full p-0.5">
                    <span class="w-3 h-3 bg-white rounded-full transform dark:translate-x-3 transition"></span>
                </span>
            </button>
        </div>
                <!-- <div class="flex items-center space-x-4">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                    </div>
                    <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-full" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                    </button>
                </div> -->
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-200 rounded text-green-700 flex items-center space-x-2" role="alert">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-200 rounded text-red-700 flex items-center space-x-2" role="alert">
                        <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-200 rounded text-red-700" role="alert">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>

    @yield('scripts')
</body>
</html>
