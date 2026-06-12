{{-- Overlay modal form gabungan produk dan resep --}}
<div id="menuModal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50 transition-all opacity-0">
    {{-- Dialog utama modal produk dan resep --}}
    <div class="bg-white dark:bg-[#0a0a0a] rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden transform scale-95 transition-all duration-300"
        id="menuModalDialog">

        <!-- Header -->
        {{-- Header modal berisi judul dan tombol tutup --}}
        <div
            class="px-8 py-5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#050505] flex justify-between items-center z-10 shrink-0">
            {{-- Identitas modal produk dan resep --}}
            <div class="flex items-center gap-3">
                {{-- Judul modal dan label kategori form --}}
                <div>
                    <h3 class="font-extrabold text-lg text-slate-900 dark:text-white leading-tight">Buat Menu Baru</h3>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Produk & Resep</p>
                </div>
            </div>
            <button type="button" onclick="closeMenuModal()"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-red-500 hover:bg-red-700 text-white transition-colors duration-200 ease-in-out">
                <x-icons.x-mark class="w-5 h-5" />
            </button>
        </div>

        <!-- Body Area -->
        {{-- Body modal berisi form produk dan resep --}}
        <div class="p-6 md:p-8 overflow-y-auto flex-1 custom-scrollbar">
            <form id="compositeForm" class="flex flex-col gap-6">

                <!-- Box 1: Data Produk -->
                {{-- Card informasi dasar produk --}}
                <div
                    class="bg-white dark:bg-[#0f172a] p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    {{-- Header section informasi dasar produk --}}
                    <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate-100 dark:border-slate-800/60">
                        <span
                            class="w-6 h-6 rounded bg-slate-800 dark:bg-white text-white dark:text-slate-900 flex justify-center items-center text-xs font-black">1</span>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm">Informasi Dasar Produk</h4>
                    </div>

                    {{-- Grid input data produk --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Field nama produk --}}
                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-bold text-slate-900 dark:text-white mb-1.5 uppercase tracking-wide">Nama
                                Produk</label>
                            <input type="text" id="prodName" required placeholder="Contoh: Kopi Susu Aren"
                                class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none transition-all text-sm font-medium dark:bg-[#050505] dark:border-slate-800 dark:focus:border-white dark:focus:ring-white dark:text-white">
                        </div>
                        {{-- Field kategori produk --}}
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-900 dark:text-white mb-1.5 uppercase tracking-wide">Kategori</label>
                            <select id="prodCategory" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none transition-all text-sm font-medium dark:bg-[#050505] dark:border-slate-800 dark:focus:border-white dark:focus:ring-white dark:text-white">
                                <option value="">Pilih Kategori</option>
                            </select>
                        </div>
                        {{-- Field harga jual produk --}}
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-900 dark:text-white mb-1.5 uppercase tracking-wide">Harga
                                Jual (Rp)</label>
                            <input type="number" id="prodPrice" required placeholder="0"
                                class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none transition-all text-sm font-medium dark:bg-[#050505] dark:border-slate-800 dark:focus:border-white dark:focus:ring-white dark:text-white">
                        </div>
                        {{-- Field status produk --}}
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-900 dark:text-white mb-1.5 uppercase tracking-wide">Status</label>
                            <select id="prodStatus" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none transition-all text-sm font-medium dark:bg-[#050505] dark:border-slate-800 dark:focus:border-white dark:focus:ring-white dark:text-white">
                                <option value="active">Aktif</option>
                                <option value="inactive">Inaktif</option>
                            </select>
                        </div>
                        {{-- Field upload foto produk --}}
                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-bold text-slate-900 dark:text-white mb-1.5 uppercase tracking-wide">Foto
                                Produk <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input type="file" id="prodImage" accept="image/*"
                                class="w-full block text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer dark:file:bg-slate-800 dark:file:text-slate-300 dark:hover:file:bg-slate-700 px-3 py-2 border border-dashed border-slate-300 rounded-xl bg-white dark:bg-[#050505] dark:border-slate-700">
                        </div>
                    </div>
                </div>

                <!-- Box 2: Komposisi Resep -->
                {{-- Card komposisi resep dan takaran bahan baku --}}
                <div
                    class="bg-white dark:bg-[#0f172a] p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col h-full">
                    {{-- Header section resep dan takaran --}}
                    <div class="flex items-center gap-2 mb-2 pb-3 border-b border-slate-100 dark:border-slate-800/60">
                        <span
                            class="w-6 h-6 rounded bg-slate-800 dark:bg-white text-white dark:text-slate-900 flex justify-center items-center text-xs font-black">2</span>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm">Resep & Takaran</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium mb-5">Tentukan bahan baku yang akan memotong stok
                        secara otomatis ketika produk ini terjual.</p>

                    {{-- Container baris bahan baku yang diisi oleh JavaScript --}}
                    <div id="ingredientsContainer" class="flex flex-col gap-3 mb-4">
                        <!-- JS akan mencetak baris resep disini -->
                    </div>

                    <button type="button" onclick="addIngredientRow()"
                        class="w-full py-3.5 mt-1 border border-dashed border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#151f32] transition-colors font-bold text-sm flex gap-2 justify-center items-center group">
                        <x-icons.plus class="w-4 h-4 text-slate-400 group-hover:scale-110 transition-transform" />
                        Tambah Bahan Baku
                    </button>
                </div>

            </form>
        </div>

        <!-- Footer -->
        {{-- Footer modal berisi tombol batal dan simpan --}}
        <div
            class="p-6 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-[#050505] flex justify-end gap-3 shrink-0">
            <button type="button" onclick="closeMenuModal()"
                class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition-all text-sm">Batal</button>
            <button type="submit" form="compositeForm"
                class="px-8 py-3 bg-green-500 hover:bg-green-600 text-white shadow-lg shadow-green-500/30 rounded-xl font-bold transition-all transform active:scale-95 text-sm flex items-center gap-2">
                <x-icons.check-circle class="w-5 h-5" /> Simpan Menu
            </button>
        </div>
    </div>
</div>
