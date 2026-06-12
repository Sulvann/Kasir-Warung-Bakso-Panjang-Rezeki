{{-- Overlay modal tambah dan edit bahan --}}
<div id="ingredientModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4 opacity-0 backdrop-blur-sm transition-all duration-200">
    {{-- Container utama modal form bahan --}}
    <div class="max-h-[90vh] w-full max-w-md scale-95 overflow-y-auto overflow-x-hidden rounded-2xl bg-white shadow-xl transition-all duration-200 dark:bg-[#0f172a]"
        id="modalDialog">
        {{-- Header modal bahan --}}
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-6 dark:border-slate-800 dark:bg-[#0a0a0a]">
            <h3 id="modalTitle" class="text-xl font-black uppercase tracking-tight text-slate-900">Tambah Bahan Setengah
                Jadi</h3>
            <button type="button" onclick="closeModal()"
                class="flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white transition-colors duration-200 ease-in-out hover:bg-red-600">
                <x-icons.x-mark class="h-5 w-5" />
            </button>
        </div>
        <form id="ingredientForm" class="space-y-5 p-6">
            <input type="hidden" id="ingredientId">
            {{-- Field nama bahan --}}
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">Nama Bahan /
                    Item</label>
                <input type="text" id="ingredientName" required placeholder="Contoh: Bakso Sapi (Grosir)"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900 dark:border-slate-800 dark:bg-[#050505] dark:text-white dark:focus:border-white dark:focus:ring-white">
            </div>
            {{-- Grid stok awal dan satuan bahan --}}
            <div class="flex gap-4">
                {{-- Field total stok awal bahan --}}
                <div class="flex-1">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">Total Stok
                        Awal</label>
                    <input type="number" id="ingredientStock" step="0.01" required min="0" placeholder="5000"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900 dark:border-slate-800 dark:bg-[#050505] dark:text-white dark:focus:border-white dark:focus:ring-white">
                </div>
                {{-- Field satuan bahan --}}
                <div class="w-1/2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">Satuan</label>
                    <select id="ingredientUnit" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900 dark:border-slate-800 dark:bg-[#050505] dark:text-white dark:focus:border-white dark:focus:ring-white">
                        <option value="Gram">Gram</option>
                        <option value="Kg">Kg</option>
                        <option value="Kantong">Kantong</option>
                        <option value="Pcs">Pcs</option>
                    </select>
                </div>
            </div>
            {{-- Field status bahan --}}
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">Status</label>
                <select id="ingredientStatus" required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900 dark:border-slate-800 dark:bg-[#050505] dark:text-white dark:focus:border-white dark:focus:ring-white">
                    <option value="active">Aktif</option>
                    <option value="inactive">Inaktif</option>
                </select>
            </div>
            {{-- Tombol aksi modal bahan --}}
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal()"
                    class="rounded-xl bg-red-500 px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-red-600">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-xl bg-green-500 px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-green-600">
                    Simpan Stok
                </button>
            </div>
        </form>
    </div>
</div>
