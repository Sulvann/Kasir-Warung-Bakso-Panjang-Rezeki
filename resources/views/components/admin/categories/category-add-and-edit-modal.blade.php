{{-- Overlay modal tambah dan edit kategori --}}
<div id="categoryModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    {{-- Container utama modal form kategori --}}
    <div class="relative w-full max-w-[500px] rounded-2xl bg-white p-8">
        <button type="button"
            class="absolute right-6 top-6 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-red-500 text-white transition-colors hover:bg-red-700"
            onclick="closeModal()">
            <x-icons.x-mark class="h-5 w-5" />
        </button>
        <h2 id="modalTitle" class="text-xl font-black text-slate-900 mb-6 uppercase tracking-tight">Tambah Kategori</h2>
        <form id="categoryForm">
            <input type="hidden" id="categoryId">
            {{-- Field nama kategori --}}
            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-900 mb-1.5 uppercase tracking-wide">Nama Kategori</label>
                <input type="text" id="categoryName" required placeholder="Contoh: Makanan" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none transition-all text-sm font-medium">
            </div>
            {{-- Field status kategori --}}
            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-900 mb-1.5 uppercase tracking-wide">Status</label>
                <select id="categoryStatus" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none transition-all text-sm font-medium">
                    <option value="active">Aktif</option>
                    <option value="inactive">Inaktif</option>
                </select>
            </div>
            {{-- Tombol aksi modal kategori --}}
            <div class="flex gap-3 justify-end mt-2">
                <button type="button" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-red-500 hover:bg-red-600 transition-colors" onclick="closeModal()">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-green-500 hover:bg-green-600 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>
