<!-- MODAL CATATAN PER PORSI -->
{{-- Overlay modal catatan per porsi --}}
<div id="modalNotePerPorsi"
    class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[110] backdrop-blur-sm p-4">
    {{-- Kotak utama modal catatan pesanan --}}
    <div
        class="bg-white dark:bg-[#111] rounded-3xl w-full max-w-sm overflow-hidden border border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col max-h-[80vh]">
        {{-- Header modal catatan dan tombol tutup --}}
        <div
            class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-[#1a1a1a]">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Catatan Pesanan</h3>
            <button onclick="closeNoteModal()"
                class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:text-slate-200 dark:hover:bg-[#222] font-bold transition-transform duration-200 ease-in-out">
                <x-icons.x-mark class="w-6 h-6" />
            </button>
        </div>
        {{-- Daftar input catatan per porsi, diisi melalui JavaScript --}}
        <div class="p-6 overflow-y-auto" id="noteModalList">
            <!-- Injected via JS -->
        </div>
        {{-- Footer modal catatan berisi tombol batal dan simpan --}}
        <div
            class="px-6 py-4 bg-slate-50 dark:bg-[#0a0a0a] border-t border-slate-100 dark:border-slate-800 flex justify-center items-center gap-3">
            <button onclick="closeNoteModal()"
                class="px-5 py-2.5 font-bold text-white bg-red-500 hover:bg-red-600 rounded-xl transition-transform duration-200 ease-in-out inline-flex items-center justify-center gap-2">
                <x-icons.x-circle class="w-5 h-5" />
                Batal
            </button>
            <button id="noteModalBtnSave"
                class="px-6 py-2.5 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition-transform duration-200 ease-in-out inline-flex items-center justify-center gap-2">
                <x-icons.check-circle class="w-5 h-5" />
                Simpan Catatan
            </button>
        </div>
    </div>
</div>
