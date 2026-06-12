<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RealKasir</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts: Inter (San Francisco Alternative) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "San Francisco", "Helvetica Neue", sans-serif !important;
        }

        /* Loading Overlay Custom CSS */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f8fafc;
            /* slate-50 */
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            /* slate-200 */
            border-top: 4px solid #0f172a;
            /* slate-900 */
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    @yield('styles')
</head>

<body
    class="bg-slate-50 dark:bg-[#000000] font-sans antialiased text-slate-800 dark:text-slate-100 flex min-h-screen transition-colors duration-200">
    <div id="loadingOverlay" class="dark:bg-[#000000] transition-colors duration-200">
        <div class="spinner dark:border-slate-700 dark:border-t-cerulean-500"></div>
    </div>

    <!-- Sidebar Tailwind -->
    <aside
        class="w-64 bg-[#000000] dark:bg-[#000000] text-[#EDEDED] flex flex-col fixed h-screen z-50 transition-colors duration-200 shadow-xl">
        <!-- Brand -->
        <div class="py-3 px-3 flex justify-center items-center border-b border-[#333333]">
            <!-- PENTING: Pastikan file gambar Anda bernama 'logo.png' dan letakkan di folder 'public/images/' -->
            <img src="{{ asset('logo.png') }}" alt="Logo Warung Bakso"
                class="w-52 h-52 object-contain hover:scale-105 transition-transform duration-300">
        </div>
        <nav class="flex flex-col flex-1 py-4 overflow-y-auto">
            <a href="/admin/dashboard"
                class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 antialiased {{ request()->is('admin/dashboard*') ? 'bg-[#DA0037] text-white font-semibold shadow-md' : 'text-slate-400 font-medium hover:bg-[#DA0037] hover:text-white' }}">
                <x-icons.home class="w-5 h-5" />
                Dashboard
            </a>

            <a href="/admin/users"
                class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 antialiased {{ request()->is('admin/users*') ? 'bg-[#DA0037] text-white font-semibold shadow-md' : 'text-slate-400 font-medium hover:bg-[#DA0037] hover:text-white' }}">
                <x-icons.users class="w-5 h-5" />
                Kelola Akun
            </a>

            <a href="/admin/categories"
                class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 antialiased {{ request()->is('admin/categories*') ? 'bg-[#DA0037] text-white font-semibold shadow-md' : 'text-slate-400 font-medium hover:bg-[#DA0037] hover:text-white' }}">
                <x-icons.folder-open class="w-5 h-5" />
                Kategori
            </a>

            <a href="{{ route('admin.stok') }}"
                class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 antialiased {{ request()->is('admin/stok*') ? 'bg-[#DA0037] text-white font-semibold shadow-md' : 'text-slate-400 font-medium hover:bg-[#DA0037] hover:text-white' }}">
                <x-icons.cube class="w-5 h-5" />
                Bahan Setengah Jadi
            </a>

            <a href="{{ route('admin.recipes') }}"
                class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 antialiased {{ request()->is('admin/recipes*') ? 'bg-[#DA0037] text-white font-semibold shadow-md' : 'text-slate-400 font-medium hover:bg-[#DA0037] hover:text-white' }}">
                <x-icons.book-open class="w-5 h-5" />
                Produk dan Resep
            </a>

            <a href="/admin/products"
                class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 antialiased {{ request()->is('admin/products*') ? 'bg-[#DA0037] text-white font-semibold shadow-md' : 'text-slate-400 font-medium hover:bg-[#DA0037] hover:text-white' }}">
                <x-icons.bowl class="w-5 h-5" />
                Produk
            </a>

            <a href="/admin/incomes"
                class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 antialiased {{ request()->is('admin/incomes*') ? 'bg-[#DA0037] text-white font-semibold shadow-md' : 'text-slate-400 font-medium hover:bg-[#DA0037] hover:text-white' }}">
                <x-icons.trending-up class="w-5 h-5" />
                Pemasukan
            </a>

            <a href="/admin/expenses"
                class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 antialiased {{ request()->is('admin/expenses*') ? 'bg-[#DA0037] text-white font-semibold shadow-md' : 'text-slate-400 font-medium hover:bg-[#DA0037] hover:text-white' }}">
                <x-icons.trending-down class="w-5 h-5" />
                Pengeluaran
            </a>

            <a href="/admin/reports"
                class="flex items-center gap-4 px-6 py-3.5 text-sm transition-all duration-200 antialiased {{ request()->is('admin/reports*') ? 'bg-[#DA0037] text-white font-semibold shadow-md' : 'text-slate-400 font-medium hover:bg-[#DA0037] hover:text-white' }}">
                <x-icons.document-chart-bar class="w-5 h-5" />
                Laporan Keuangan
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 ml-64 min-w-0 bg-slate-50 dark:bg-[#000000] min-h-screen transition-colors duration-200">
        @include('layouts.navigation')

        <div class="p-8">
            @yield('content')
        </div>
    </main>

    <script>
        document.getElementById('loadingOverlay').style.display = 'none';

        function logout() {
            if (confirm('Yakin ingin keluar dari sistem?')) {
                fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                }).finally(() => {
                    window.location.href = '/login';
                });
            }
        }
    </script>

    @yield('scripts')
</body>

</html>
