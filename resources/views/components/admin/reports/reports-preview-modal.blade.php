{{-- Overlay modal pratinjau laporan --}}
<div id="previewModal" tabindex="-1" aria-hidden="true"
    class="fixed inset-0 z-[100] hidden items-center justify-center overflow-auto bg-slate-900/50 backdrop-blur-sm transition-opacity">
    {{-- Wrapper posisi dan ukuran modal pratinjau --}}
    <div class="relative mx-auto mb-10 mt-10 w-full max-w-6xl p-4 transition-all">
        {{-- Container utama modal pratinjau --}}
        <div class="relative flex max-h-[90vh] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            {{-- Header modal pratinjau --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-white p-5">
                <h3 class="flex items-center text-xl font-bold text-slate-800">
                    <x-icons.document-text class="mr-3 h-6 w-6 text-blue-600" />
                    Preview Laporan Excel
                </h3>
                <button type="button" id="btnCloseModalTop"
                    class="ms-auto inline-flex h-9 w-9 items-center justify-center rounded-lg bg-transparent text-sm text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-900">
                    <x-icons.x-mark class="h-6 w-6" />
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            {{-- Body modal berisi loading dan hasil pratinjau --}}
            <div class="flex-1 overflow-y-auto bg-slate-50/50 p-6" id="previewContent">
                {{-- Status loading saat mengambil data laporan --}}
                <div class="py-12 text-center" id="previewLoading" style="display: none;">
                    {{-- Wrapper animasi loading --}}
                    <div class="relative inline-block h-12 w-12">
                        {{-- Lingkaran dasar loading --}}
                        <div class="absolute left-0 top-0 h-full w-full rounded-full border-4 border-slate-200"></div>
                        {{-- Lingkaran animasi loading --}}
                        <div class="absolute left-0 top-0 h-full w-full animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
                    </div>
                    <p class="mt-4 font-medium text-slate-500">Mengambil data laporan...</p>
                </div>

                {{-- Container hasil pratinjau laporan dari AJAX --}}
                <div id="previewResult" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    {{-- Preview content will be injected here via AJAX --}}
                </div>
            </div>

            {{-- Footer modal pratinjau --}}
            <div class="flex items-center justify-end border-t border-slate-100 bg-white p-5">
                <button type="button" id="btnCloseModalBottom"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                    Tutup Pratinjau
                </button>
            </div>
        </div>
    </div>
</div>
