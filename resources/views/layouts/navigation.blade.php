<div
    class="bg-white dark:bg-[#000000] px-8 py-5 shadow-sm flex justify-end items-center border-b border-slate-200 dark:border-slate-800 transition-colors duration-200">

    <!-- Area Kanan - Memberikan jarak antar menu yang lebih besar -->
    <div class="flex items-center gap-6 lg:gap-8">

        <!-- 1. Blok Tanggal -->
        <div class="flex items-center px-4 py-2 bg-[#E6F0FF] text-[#0A58CA] dark:bg-blue-900/40 dark:text-blue-300 rounded-xl"
            title="Tanggal Hari Ini">
            <x-heroicon-o-calendar class="w-5 h-5 mr-3" />
            <span id="currentDate" class="font-semibold text-sm">Memuat...</span>
        </div>

        <!-- 2. Blok Waktu -->
        <div class="flex items-center px-4 py-2 bg-[#F3E8FF] text-[#7E22CE] dark:bg-purple-900/40 dark:text-purple-300 rounded-xl"
            title="Waktu Saat Ini">
            <x-heroicon-o-clock class="w-5 h-5 mr-3 hidden" />
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span id="currentTime" class="font-bold text-sm tabular-nums">Memuat...</span>
        </div>

        <!-- 3. Tombol Dark Mode -->
        <button id="themeToggle" title="Ganti Mode"
            class="p-4 rounded-xl font-bold border border-slate-100 dark:border-slate-800 bg-white dark:bg-[#050505] text-slate-500 hover:text-slate-800 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-[#0a0a0a] shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] transition-colors duration-500 focus:outline-none group">
            <!-- Mode Terang menampilkan Matahari -->
            <x-heroicon-o-sun class="w-6 h-6 block dark:hidden transition-colors duration-500" id="iconSun" />
            <!-- Mode Gelap menampilkan Bulan -->
            <x-heroicon-o-moon class="w-6 h-6 hidden dark:block transition-colors duration-500" id="iconMoon" />
        </button>

        <!-- Garis Pemisah (Divider) -->
        <div class="h-10 w-px bg-slate-200 dark:bg-slate-800"></div>

        <!-- 3. Profil Akun -->
        <div
            class="flex items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-[#050505] p-2 pr-5 rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-800 transition-all">
            <div
                class="flex items-center justify-center w-12 h-12 rounded-full bg-[#1D4ED8] text-white font-bold text-xl shadow-md mr-4 group-hover:scale-105 transition-transform">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="text-left flex flex-col justify-center">
                <p
                    class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-0.5 group-hover:text-blue-600 transition-colors whitespace-nowrap">
                    {{ auth()->user()->name ?? 'Administrator' }}
                </p>
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                    {{ auth()->user()->role ?? 'Admin' }}
                </p>
            </div>
        </div>

        <!-- 5. Tombol Logout -->
        <form method="POST" action="/logout" class="inline">
            @csrf
            <button type="submit" title="Logout"
                class="flex items-center gap-2 px-6 py-2 rounded-xl font-bold bg-[#FFF1F2] border border-[#FECDD3] text-[#E11D48] dark:bg-red-900/40 dark:border-red-800 dark:text-red-400 hover:bg-[#FFE4E6] dark:hover:bg-red-900/60 shadow-[0_2px_10px_-3px_rgba(225,29,72,0.1)] transition-all focus:outline-none">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
                <span>Logout</span>
            </button>
        </form>

    </div>
</div>

<script>
    // System Waktu
    function updateClock() {
        const now = new Date();

        // Format Tanggal (Contoh: Minggu, 06 - Maret - 2026)
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const dayName = days[now.getDay()];
        const day = String(now.getDate()).padStart(2, '0');
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        document.getElementById('currentDate').innerText = `${dayName}, ${day} - ${month} - ${year}`;

        // Format Waktu (Contoh: 14:05:30)
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('currentTime').innerText = `${hours}:${minutes}:${seconds}`;
    }

    updateClock();
    setInterval(updateClock, 1000);

    // System Dark Mode
    const themeToggleBtn = document.getElementById('themeToggle');

    // Cek preferensi user sebelumnya
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    themeToggleBtn.addEventListener('click', function () {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
        }
    });

    // Dummy logout function (replace with actual logic if needed)
    function logout() {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            // Logika logout sesungguhnya, misalnya via form submit
            console.log('Logging out...');
            // window.location.href = '/logout'; 
        }
    }
</script>