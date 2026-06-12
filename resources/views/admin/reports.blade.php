@extends('layouts.admin')

@section('content')
    {{-- Header halaman laporan keuangan --}}
    <div class="mb-8 flex justify-between items-end">
        {{-- Judul dan deskripsi halaman --}}
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan Keuangan</h1>
            <p class="text-slate-500 mt-1 text-sm">Unduh laporan pemasukan dan pengeluaran dalam format Excel.</p>
        </div>
    </div>

    {{-- Layout utama form laporan dan kartu informasi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Form Pemilihan Tanggal -->
        {{-- Card form pemilihan periode laporan --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Isi card form periode laporan --}}
            <div class="p-6">
                <form action="{{ route('admin.reports.export') }}" method="POST" id="reportForm" class="space-y-5">
                    @csrf

                    {{-- Field tanggal awal laporan --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Dari Tanggal</label>
                        {{-- Wrapper input tanggal awal dengan ikon --}}
                        <div class="relative">
                            {{-- Ikon kalender tanggal awal --}}
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-icons.calendar class="h-5 w-5 text-slate-400" />
                            </div>
                            <input type="date" name="start_date" id="start_date" required
                                class="pl-10 w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 border py-2.5 px-3 text-slate-700 text-sm"
                                value="{{ date('Y-m-01') }}">
                        </div>
                    </div>

                    {{-- Field tanggal akhir laporan --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Sampai Tanggal</label>
                        {{-- Wrapper input tanggal akhir dengan ikon --}}
                        <div class="relative">
                            {{-- Ikon kalender tanggal akhir --}}
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-icons.calendar-days class="h-5 w-5 text-slate-400" />
                            </div>
                            <input type="date" name="end_date" id="end_date" required
                                class="pl-10 w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 border py-2.5 px-3 text-slate-700 text-sm"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    {{-- Area tombol preview dan unduh laporan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <button type="button" id="btnPreview"
                            class="flex justify-center items-center w-full py-2.5 px-4 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <x-icons.eye class="w-5 h-5 mr-2 text-slate-500" />
                            Tampilkan Modal
                        </button>

                        <button type="submit"
                            class="flex justify-center items-center w-full py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-slate-800 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-colors">
                            <x-icons.document-arrow-down class="w-5 h-5 mr-2 text-white" />
                            Unduh Laporan Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kartu Info -->
        {{-- Wrapper tinggi penuh untuk kartu informasi laporan --}}
        <div class="h-full">
            {{-- Card informasi cakupan laporan --}}
            <div class="bg-blue-50 rounded-xl border border-blue-100 h-full">
                {{-- Isi kartu informasi laporan --}}
                <div class="p-6">
                    {{-- Header kartu informasi laporan --}}
                    <div class="flex items-center mb-4 text-blue-800">
                        <x-icons.information-circle class="w-6 h-6 mr-2" />
                        <h5 class="text-base font-bold">Informasi Laporan</h5>
                    </div>
                    <p class="text-sm font-medium text-blue-800/80 mb-3">Laporan yang diunduh mencakup:</p>
                    <ul class="space-y-2 text-sm text-blue-800/70 list-disc list-inside ml-1">
                        <li>Daftar Pemasukan (Transaksi Sukses)</li>
                        <li>Daftar Pengeluaran Operasional</li>
                        <li>Produk, metode pembayaran, dan pengeluaran terbesar</li>
                        <li>Tren penjualan periode terpilih dalam bentuk tabel</li>
                        <li>Ringkasan Total & Laba Rugi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Preview Modal (Tailwind Version) -->
    {{-- Overlay modal pratinjau laporan --}}
    <div id="previewModal" tabindex="-1" aria-hidden="true" 
         class="fixed inset-0 z-[100] hidden items-center justify-center overflow-auto bg-slate-900/50 backdrop-blur-sm transition-opacity">
        
        {{-- Wrapper posisi dan ukuran modal pratinjau --}}
        <div class="relative w-full max-w-6xl p-4 mx-auto mt-10 mb-10 transition-all transform">
            {{-- Container utama modal pratinjau --}}
            <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                {{-- Header modal pratinjau --}}
                <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-white">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center">
                        <x-icons.document-text class="w-6 h-6 mr-3 text-blue-600" />
                        Pratinjau Laporan Keuangan
                    </h3>
                    <button type="button" id="btnCloseModalTop"
                            class="text-slate-400 bg-transparent hover:bg-slate-100 hover:text-slate-900 rounded-lg text-sm w-9 h-9 ms-auto inline-flex justify-center items-center transition-colors">
                        <x-icons.x-mark class="w-6 h-6" />
                        <span class="sr-only">Tutup modal</span>
                    </button>
                </div>

                <!-- Modal Body -->
                {{-- Body modal berisi loading dan hasil pratinjau --}}
                <div class="p-6 overflow-y-auto bg-slate-50/50 flex-1" id="previewContent">
                    {{-- Status loading saat mengambil data laporan --}}
                    <div class="text-center py-12" id="previewLoading" style="display: none;">
                        {{-- Wrapper animasi loading --}}
                        <div class="inline-block relative w-12 h-12">
                            {{-- Lingkaran dasar loading --}}
                            <div class="absolute top-0 left-0 w-full h-full border-4 border-slate-200 rounded-full"></div>
                            {{-- Lingkaran animasi loading --}}
                            <div class="absolute top-0 left-0 w-full h-full border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
                        </div>
                        <p class="mt-4 text-slate-500 font-medium">Mengambil data laporan...</p>
                    </div>
                    
                    {{-- Container hasil pratinjau laporan dari AJAX --}}
                    <div id="previewResult" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <!-- Preview content will be injected here via AJAX -->
                    </div>
                </div>

                <!-- Modal Footer -->
                {{-- Footer modal pratinjau --}}
                <div class="flex items-center justify-end p-5 border-t border-slate-100 bg-white">
                    <button type="button" id="btnCloseModalBottom"
                            class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-900 focus:z-10 focus:ring-4 focus:ring-slate-100 transition-colors">
                        Tutup Pratinjau
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnPreview = document.getElementById('btnPreview');
            const previewModal = document.getElementById('previewModal');
            const btnCloseModalTop = document.getElementById('btnCloseModalTop');
            const btnCloseModalBottom = document.getElementById('btnCloseModalBottom');
            
            const previewContent = document.getElementById('previewResult');
            const previewLoading = document.getElementById('previewLoading');

            // Fungsi Buka Modal
            function openModal() {
                previewModal.classList.remove('hidden');
                previewModal.classList.add('flex');
                previewModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden'; // cegah scroll body saat modal terbuka
                btnCloseModalTop.focus();
            }

            // Fungsi Tutup Modal
            function closeModal() {
                btnPreview.focus();
                previewModal.setAttribute('aria-hidden', 'true');
                previewModal.classList.add('hidden');
                previewModal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            btnCloseModalTop.addEventListener('click', closeModal);
            btnCloseModalBottom.addEventListener('click', closeModal);
            
            // Tutup jika klik area gelap luar modal
            previewModal.addEventListener('click', function(e) {
                if(e.target === previewModal) {
                    closeModal();
                }
            });


            btnPreview.addEventListener('click', function () {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); // Ambil dari header meta global

                if (!startDate || !endDate) {
                    alert('Silakan pilih rentang tanggal laporan terlebih dahulu.');
                    return;
                }

                // Tampilkan Modal dan Status Loading
                previewContent.innerHTML = '';
                previewLoading.style.display = 'block';
                openModal();

                // Fetch data preview
                fetch('{{ route("admin.reports.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'text/html'
                    },
                    body: JSON.stringify({
                        start_date: startDate,
                        end_date: endDate
                    })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        previewLoading.style.display = 'none';
                        previewContent.innerHTML = html;
                    })
                    .catch(error => {
                        previewLoading.style.display = 'none';
                        previewContent.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat memuat laporan. Pastikan koneksi internet stabil dan coba lagi.</div>';
                        console.error('Error fetching preview:', error);
                    });
            });
        });
    </script>
@endsection
