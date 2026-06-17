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
                            class="flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm ring-0 transition-transform duration-150 ease-in-out hover:-translate-y-0.5 active:scale-95 focus:border-slate-300 focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0">
                            <x-icons.eye class="w-5 h-5 mr-2 text-slate-500" />
                            Preview Excel
                        </button>

                        <button type="submit"
                            class="flex w-full items-center justify-center rounded-lg border border-transparent bg-slate-800 px-4 py-2.5 text-sm font-medium text-white shadow-sm ring-0 transition-transform duration-150 ease-in-out hover:-translate-y-0.5 active:scale-95 focus:border-transparent focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0">
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
    {{-- Component modal pratinjau laporan --}}
    <x-admin.reports.reports-preview-modal />

    {{-- Component modal alert dan konfirmasi --}}
    <x-admin.reports.alert-and-confirm-modal />
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

            // Membuka modal alert admin.
            function openAlertModal() {
                const alertModal = document.getElementById('alertModal');
                const content = document.getElementById('alertModalContent');

                alertModal.classList.remove('hidden');
                alertModal.classList.add('flex');

                requestAnimationFrame(() => {
                    alertModal.classList.remove('opacity-0');
                    alertModal.classList.add('opacity-100');
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                });
            }

            // Menutup modal alert admin.
            function closeAlertModal(resolve, result = true) {
                const alertModal = document.getElementById('alertModal');
                const content = document.getElementById('alertModalContent');

                alertModal.classList.remove('opacity-100');
                alertModal.classList.add('opacity-0');
                content.classList.remove('scale-100');
                content.classList.add('scale-95');

                setTimeout(() => {
                    alertModal.classList.add('hidden');
                    alertModal.classList.remove('flex');
                    resolve(result);
                }, 200);
            }

            // Menampilkan modal alert satu tombol.
            function showAlertDialog(message) {
                return new Promise((resolve) => {
                    const confirmActions = document.getElementById('alertConfirmActions');
                    const okActions = document.getElementById('alertOkActions');

                    document.getElementById('alertMessage').textContent = message;
                    confirmActions.classList.add('hidden');
                    confirmActions.classList.remove('flex');
                    okActions.classList.remove('hidden');

                    openAlertModal();
                    document.getElementById('modalBtnOk').onclick = () => closeAlertModal(resolve, true);
                });
            }

            // Mengaktifkan tab sheet pada preview laporan bergaya Excel.
            function initReportPreviewTabs() {
                const wrapper = previewContent.querySelector('[data-report-preview-tabs]');
                if (!wrapper) return;

                const tabs = wrapper.querySelectorAll('[data-report-tab]');
                const panels = wrapper.querySelectorAll('[data-report-tab-panel]');

                const setActiveTab = (target) => {
                    tabs.forEach((tab) => {
                        const isActive = tab.dataset.reportTab === target;
                        tab.classList.toggle('border-blue-500', isActive);
                        tab.classList.toggle('bg-white', isActive);
                        tab.classList.toggle('text-blue-700', isActive);
                        tab.classList.toggle('shadow-sm', isActive);
                        tab.classList.toggle('border-transparent', !isActive);
                        tab.classList.toggle('bg-slate-100', !isActive);
                        tab.classList.toggle('text-slate-600', !isActive);
                    });

                    panels.forEach((panel) => {
                        panel.classList.toggle('hidden', panel.dataset.reportTabPanel !== target);
                    });
                };

                tabs.forEach((tab) => {
                    tab.addEventListener('click', () => setActiveTab(tab.dataset.reportTab));
                });

                setActiveTab(tabs[0]?.dataset.reportTab);
            }

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
                btnPreview.blur();
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


            btnPreview.addEventListener('click', async function () {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); // Ambil dari header meta global

                if (!startDate || !endDate) {
                    await showAlertDialog('Silakan pilih rentang tanggal laporan terlebih dahulu.');
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
                        initReportPreviewTabs();
                    })
                    .catch(error => {
                        previewLoading.style.display = 'none';
                        previewContent.innerHTML = '<div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-600">Terjadi kesalahan saat memuat laporan. Pastikan koneksi internet stabil dan coba lagi.</div>';
                        console.error('Error fetching preview:', error);
                    });
            });
        });
    </script>
@endsection
