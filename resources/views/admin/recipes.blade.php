@extends('layouts.admin')

@section('content')
    {{-- Header halaman produksi dan resep --}}
    <div class="mb-4 flex items-center justify-between">
        {{-- Judul dan deskripsi halaman --}}
        <div>
            <h1 class="m-0 text-2xl font-bold tracking-tight text-slate-900">Produksi & Resep</h1>
            <p class="mb-0 mt-1 text-sm font-medium text-slate-500">Kelola standar resep dan menu jual Anda.</p>
        </div>
        <button type="button" onclick="openMenuModal()"
            class="rounded-lg bg-[#007bff] px-6 py-3 text-sm font-semibold text-white transition hover:opacity-80 active:scale-95">
            + Buat Menu & Resep Baru
        </button>
    </div>

    {{-- Area filter produk resep --}}
    <div class="mb-6 flex flex-wrap items-end gap-4">
        {{-- Input filter status --}}
        <div>
            <label for="statusFilter"
                class="mb-2 flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wide text-slate-500">
                <x-icons.filter class="h-4 w-4" />
                Filter Status
            </label>
            <select id="statusFilter"
                class="h-[42px] min-w-[180px] cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-900 outline-none">
                <option value="all">Semua</option>
                <option value="active">Aktif</option>
                <option value="inactive">Inaktif</option>
            </select>
        </div>

        {{-- Input filter kategori --}}
        <div>
            <label for="categoryFilter"
                class="mb-2 flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wide text-slate-500">
                <x-icons.bookmark class="h-4 w-4" />
                Filter Kategori
            </label>
            <select id="categoryFilter"
                class="h-[42px] min-w-[220px] cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-900 outline-none">
                <option value="all">Semua Kategori</option>
            </select>
        </div>
    </div>

    {{-- Grid daftar produk dan resep yang diisi oleh JavaScript --}}
    <div id="productsGrid" class="grid grid-cols-1 gap-6 text-sm font-medium text-slate-500 md:grid-cols-2 lg:grid-cols-3">
        Loading...
    </div>

    {{-- Component modal tambah dan edit produk beserta resep --}}
    <x-admin.recipes.recipe-add-and-edit-modal />

    {{-- Component modal alert dan konfirmasi --}}
    <x-admin.recipes.alert-and-confirm-modal />
@endsection

@section('scripts')
    <script>
        // State utama halaman resep.
        let products = [], masterIngredients = [], categories = [];

        // Endpoint API yang dipakai halaman produk dan resep.
        const API_RECIPE = '/admin/api/product-recipes';
        const API_ING = '/admin/api/ingredients';
        const API_CAT = '/admin/api/categories';
        const API_COMPOSITE = '/admin/api/product-recipes';
        const API_PRODUCT = '/admin/api/product-recipes';

        // Icon component yang dirender sebagai string agar bisa dipakai di template JavaScript.
        const editIcon = @json(view('components.icons.pencil-square', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'w-4 h-4'])])->render());
        const deleteIcon = @json(view('components.icons.trash', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'w-4 h-4'])])->render());
        const removeIcon = @json(view('components.icons.trash', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'w-5 h-5'])])->render());
        const warningIcon = @json(view('components.icons.warning-triangle', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'w-4 h-4 text-amber-500'])])->render());
        const saveIcon = @json(view('components.icons.check-circle', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'w-5 h-5'])])->render());
        const emptyImageIcon = @json(view('components.icons.empty-document', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'w-8 h-8 text-slate-300'])])->render());
        const recipeIcon = @json(view('components.icons.book-open', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'w-4 h-4 text-slate-400'])])->render());
        const ingredientIcon = @json(view('components.icons.cube', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'w-5 h-5'])])->render());

        // Menentukan status efektif produk berdasarkan status produk dan kategori.
        function getEffectiveProductStatus(product) {
            const categoryInactive = product.category && product.category.status === 'inactive';

            return product.status === 'active' && !categoryInactive ? 'active' : 'inactive';
        }

        // Membuat label status yang menjelaskan jika produk aktif tetapi kategorinya inaktif.
        function getProductStatusLabel(product) {
            if (product.status === 'active' && product.category && product.category.status === 'inactive') {
                return 'Inaktif (Kategori)';
            }

            return getEffectiveProductStatus(product) === 'active' ? 'Aktif' : 'Inaktif';
        }

        // Membuat badge status produk dalam format Tailwind.
        function statusBadge(status, label = null) {
            const isActive = status === 'active';
            const classes = isActive
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-700';

            return `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-extrabold ${classes}">${label || (isActive ? 'Aktif' : 'Inaktif')}</span>`;
        }

        // Mengurutkan produk aktif di atas produk inaktif.
        function sortActiveFirst(items) {
            return [...items].sort((a, b) => (getEffectiveProductStatus(a) === 'inactive') - (getEffectiveProductStatus(b) === 'inactive'));
        }

        // Memformat angka takaran bahan baku agar ramah dibaca.
        function formatQuantity(value) {
            const number = Number(value);
            if (!Number.isFinite(number)) return value ?? '';

            return number.toLocaleString('id-ID', {
                maximumFractionDigits: 2
            });
        }

        // Memformat harga produk ke format rupiah.
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        };

        // Menampilkan modal konfirmasi Ya/Tidak.
        function showConfirmDialog(message) {
            return new Promise((resolve) => {
                const alertModal = document.getElementById('alertModal');
                const content = document.getElementById('alertModalContent');
                const confirmActions = document.getElementById('alertConfirmActions');
                const okActions = document.getElementById('alertOkActions');

                document.getElementById('alertMessage').textContent = message;
                confirmActions.classList.remove('hidden');
                confirmActions.classList.add('flex');
                okActions.classList.add('hidden');

                alertModal.classList.remove('hidden');
                alertModal.classList.add('flex');
                requestAnimationFrame(() => {
                    alertModal.classList.remove('opacity-0');
                    alertModal.classList.add('opacity-100');
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                });

                const closeAndResolve = (result) => {
                    alertModal.classList.remove('opacity-100');
                    alertModal.classList.add('opacity-0');
                    content.classList.remove('scale-100');
                    content.classList.add('scale-95');
                    setTimeout(() => {
                        alertModal.classList.add('hidden');
                        alertModal.classList.remove('flex');
                        resolve(result);
                    }, 200);
                };

                document.getElementById('modalBtnTidak').onclick = () => closeAndResolve(false);
                document.getElementById('modalBtnYa').onclick = () => closeAndResolve(true);
            });
        }

        // Menampilkan modal alert satu tombol.
        function showAlertDialog(message) {
            return new Promise((resolve) => {
                const alertModal = document.getElementById('alertModal');
                const content = document.getElementById('alertModalContent');
                const confirmActions = document.getElementById('alertConfirmActions');
                const okActions = document.getElementById('alertOkActions');

                document.getElementById('alertMessage').textContent = message;
                confirmActions.classList.add('hidden');
                confirmActions.classList.remove('flex');
                okActions.classList.remove('hidden');

                alertModal.classList.remove('hidden');
                alertModal.classList.add('flex');
                requestAnimationFrame(() => {
                    alertModal.classList.remove('opacity-0');
                    alertModal.classList.add('opacity-100');
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                });

                const closeAndResolve = () => {
                    alertModal.classList.remove('opacity-100');
                    alertModal.classList.add('opacity-0');
                    content.classList.remove('scale-100');
                    content.classList.add('scale-95');
                    setTimeout(() => {
                        alertModal.classList.add('hidden');
                        alertModal.classList.remove('flex');
                        resolve();
                    }, 200);
                };

                document.getElementById('modalBtnOk').onclick = () => closeAndResolve();
            });
        }

        // Inisialisasi data awal halaman setelah DOM siap.
        document.addEventListener('DOMContentLoaded', async () => {
            await Promise.all([loadIngredients(), loadCategories(), loadProducts()]);
        });

        // Render ulang grid saat filter status berubah.
        document.getElementById('statusFilter').addEventListener('change', renderGrid);
        document.getElementById('categoryFilter').addEventListener('change', renderGrid);

        // Mengambil data master bahan baku dari API.
        async function loadIngredients() {
            const res = await fetch(API_ING, { headers: { 'Accept': 'application/json' } });
            masterIngredients = (await res.json()).data;
        }

        // Mengambil data kategori dari API.
        async function loadCategories() {
            const res = await fetch(API_CAT, { headers: { 'Accept': 'application/json' } });
            categories = (await res.json()).data;
            renderCategoryOptions();
            renderCategoryFilterOptions();
        }

        // Mengisi pilihan kategori pada dropdown filter halaman.
        function renderCategoryFilterOptions() {
            const filter = document.getElementById('categoryFilter');
            filter.innerHTML = '<option value="all">Semua Kategori</option>';

            categories.forEach(category => {
                filter.innerHTML += `<option value="${category.category_id}">${category.name}${category.status === 'inactive' ? ' - Inaktif' : ''}</option>`;
            });
        }

        // Mengisi pilihan kategori pada form produk.
        function renderCategoryOptions(selectedId = null) {
            const sel = document.getElementById('prodCategory');
            sel.innerHTML = '<option value="">Pilih Kategori</option>';

            categories.forEach(c => {
                const isSelected = Number(c.category_id) === Number(selectedId);
                if (c.status === 'active' || isSelected) {
                    sel.innerHTML += `<option value="${c.category_id}" ${isSelected ? 'selected' : ''} ${c.status === 'inactive' && !isSelected ? 'disabled' : ''}>${c.name}${c.status === 'inactive' ? ' - Inaktif' : ''}</option>`;
                }
            });
        }

        // Mengambil data produk beserta resep dan merender grid.
        async function loadProducts() {
            const res = await fetch(API_RECIPE, { headers: { 'Accept': 'application/json' } });
            products = sortActiveFirst((await res.json()).data);
            renderGrid();
        }

        // Menghitung potensi porsi berdasarkan stok bahan dan takaran resep.
        function calculateMaxYield(ingredients) {
            if (!ingredients || ingredients.length === 0) return 0;
            let porsi = Infinity;
            ingredients.forEach(ing => {
                const m = masterIngredients.find(x => x.ingredient_id === ing.ingredient_id);
                if (m && ing.pivot.quantity > 0) {
                    porsi = Math.min(porsi, Math.floor(m.stock / ing.pivot.quantity));
                }
            });
            return porsi === Infinity ? 0 : porsi;
        }

        // Merender kartu produk dan komposisi resep ke dalam grid.
        function renderGrid() {
            const grid = document.getElementById('productsGrid');
            const selectedStatus = document.getElementById('statusFilter').value;
            const selectedCategory = document.getElementById('categoryFilter').value;
            const filteredProducts = products.filter(product => {
                const statusMatches = selectedStatus === 'all' || getEffectiveProductStatus(product) === selectedStatus;
                const categoryMatches = selectedCategory === 'all' || Number(product.category_id) === Number(selectedCategory);

                return statusMatches && categoryMatches;
            });

            if (!filteredProducts.length) return grid.innerHTML = '<div class="col-span-2 py-12 text-center text-sm font-medium text-slate-500">Belum ada menu yang sesuai dengan filter.</div>';

            grid.innerHTML = filteredProducts.map(p => {
                const yieldAmt = calculateMaxYield(p.ingredients);
                const effectiveStatus = getEffectiveProductStatus(p);
                
                // Image handling
                let imgHtml = '';
                if (p.image) {
                    imgHtml = `<img src="/storage/${p.image}" class="w-20 h-20 rounded-xl object-cover border border-slate-200 shrink-0 shadow-sm" alt="Foto">`;
                } else {
                    imgHtml = `<div class="w-20 h-20 rounded-xl bg-slate-50 border border-slate-200 flex justify-center items-center shrink-0 shadow-sm">${emptyImageIcon}</div>`;
                }

                // Category handling
                const categoryName = p.category ? p.category.name : 'Tanpa Kategori';

                // Ingredients handling
                let ingListHtml = '<div class="text-xs text-slate-400 italic py-2 text-center">Tidak ada resep (Stok Bebas)</div>';
                if (p.ingredients && p.ingredients.length > 0) {
                    // Maksimal 2 item terlihat tanpa scroll (~72px max-height)
                    const liItems = p.ingredients.map(ing => `
                        <li class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0">
                            <span class="flex truncate items-center gap-1.5 pr-2 font-semibold text-slate-700">${ing.name} ${ing.status === 'inactive' ? warningIcon : ''}</span>
                            <span class="shrink-0 rounded border border-slate-200 bg-white px-2 py-0.5 font-bold text-slate-600 shadow-sm">${formatQuantity(ing.pivot.quantity)} ${ing.unit}</span>
                        </li>
                    `).join('');
                    ingListHtml = `<ul class="max-h-20 overflow-y-auto pr-2 text-xs custom-scrollbar">${liItems}</ul>`;
                }

                return `<div class="bg-white border border-slate-200 p-5 rounded-2xl flex flex-col gap-5 shadow-sm hover:shadow-md transition-shadow">
                            <!-- Header: Image, Title, Actions -->
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex items-center gap-4">
                                    ${imgHtml}
                                    <div class="flex flex-col justify-center">
                                        <span class="mb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500">${categoryName}</span>
                                        <h3 class="mb-1 text-lg font-extrabold capitalize leading-tight text-slate-900">${p.name}</h3>
                                        <div class="text-sm font-extrabold text-emerald-500">${formatRupiah(p.price)}</div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2 shrink-0">
                                    <button onclick="editRecipe(${p.product_id})" class="p-2 text-slate-500 bg-slate-50 hover:bg-slate-100 hover:text-slate-700 rounded-xl transition-all duration-200 border border-slate-200" title="Edit">
                                        ${editIcon}
                                    </button>
                                    <button onclick="deleteRecipe(${p.product_id})" class="p-2 text-red-500 bg-red-50 hover:bg-red-100 hover:text-red-700 rounded-xl transition-all duration-200 border border-red-100" title="Hapus">
                                        ${deleteIcon}
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Recipe Section -->
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3">
                                <div class="flex items-center gap-2 mb-2 border-b border-slate-200 pb-2">
                                    ${recipeIcon}
                                    <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Komposisi Resep</h4>
                                </div>
                                ${ingListHtml}
                            </div>
                            
                            <!-- Footer -->
                            <div class="pt-4 mt-auto border-t border-slate-100 flex justify-between items-center">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Potensi Tersedia:</span>
                                <span class="text-lg font-extrabold tracking-tight ${yieldAmt > 0 ? 'text-black' : 'text-red-500'}">${yieldAmt} Porsi</span>
                            </div>
                            <div class="pt-3 mt-3 border-t border-slate-100 flex justify-between items-center">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Status:</span>
                                ${statusBadge(effectiveStatus, getProductStatusLabel(p))}
                            </div>
                        </div>`;
            }).join('');
        }

        // Membuka modal tambah produk dan menyiapkan form kosong.
        function openMenuModal() {
            document.getElementById('compositeForm').reset();
            document.getElementById('ingredientsContainer').innerHTML = '';
            document.getElementById('prodStatus').value = 'active';
            renderCategoryOptions();

            // Hapus hidden ID kalau ada sisa bekas Edit
            const oldId = document.getElementById('prodId');
            if (oldId) oldId.remove();

            addIngredientRow();

            const modal = document.getElementById('menuModal');
            const dialog = document.getElementById('menuModalDialog');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Animasi pop-in
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                dialog.classList.remove('scale-95');
                dialog.classList.add('scale-100');
            }, 10);
        }

        // Membuka modal edit dan mengisi form dengan data produk terpilih.
        function editRecipe(id) {
            const prod = products.find(p => p.product_id === id);
            if (!prod) return;

            // Populate basic info
            renderCategoryOptions(prod.category_id);
            document.getElementById('prodName').value = prod.name;
            document.getElementById('prodCategory').value = prod.category_id;
            document.getElementById('prodPrice').value = prod.price;
            document.getElementById('prodStatus').value = prod.status;

            // Re-populate ingredients
            const container = document.getElementById('ingredientsContainer');
            container.innerHTML = '';

            if (prod.ingredients && prod.ingredients.length > 0) {
                prod.ingredients.forEach(ing => {
                    const rowId = 'edit-' + Math.floor(Math.random() * 10000);
                    const options = masterIngredients.map(i => {
                        const isSelected = i.ingredient_id === ing.ingredient_id;
                        const isInactiveIngredient = i.status === 'inactive';
                        return `<option value="${i.ingredient_id}" ${isSelected ? 'selected' : ''} ${isInactiveIngredient && !isSelected ? 'disabled' : ''}>${i.name} (${i.unit})${isInactiveIngredient ? ' - Inaktif' : ''}</option>`;
                    }).join('');

                    const html = `
                                                        <div class="ing-row flex items-center gap-3 bg-slate-50 dark:bg-[#050505] border border-slate-200 dark:border-slate-800 p-3 rounded-xl transition-all" id="row-${rowId}">
                                                            <div class="w-10 h-10 rounded-lg bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 flex justify-center items-center text-slate-400 shrink-0">
                                                                ${ingredientIcon}
                                                            </div>
                                                            <div class="flex-1">
                                                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wide mb-1.5 ml-1">Bahan Baku</label>
                                                                <select class="ing-select w-full bg-white border border-slate-300 rounded-xl px-3 py-2 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none text-sm font-medium text-slate-700 dark:bg-[#050505] dark:border-slate-800 dark:focus:border-white dark:focus:ring-white dark:text-white" required>
                                                                    <option value="">Pilih Bahan Baku</option>
                                                                    ${options}
                                                                </select>
                                                            </div>
                                                            <div class="w-24 shrink-0 px-2 border-l border-slate-200 dark:border-slate-800">
                                                                 <label class="block text-center text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wide mb-1.5">Takaran</label>
                                                                 <input type="number" step="0.01" value="${formatQuantity(ing.pivot.quantity).replace(/\./g, '').replace(',', '.')}" class="ing-qty w-full bg-white border border-slate-300 rounded-xl px-2 py-2 text-center font-bold text-slate-700 dark:text-white focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none text-sm placeholder-slate-300 dark:focus:border-white dark:focus:ring-white" required placeholder="0.0">
                                                            </div>
                                                            <button type="button" onclick="document.getElementById('row-${rowId}').remove()" class="w-10 h-10 p-0 rounded-lg text-slate-400 hover:text-white hover:bg-red-500 dark:hover:bg-red-600 flex justify-center items-center transition-colors shrink-0">
                                                                ${removeIcon}
                                                            </button>
                                                        </div>
                                                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });
            } else {
                addIngredientRow();
            }

            let idInput = document.getElementById('prodId');
            if (!idInput) {
                idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.id = 'prodId';
                idInput.name = 'id';
                document.getElementById('compositeForm').appendChild(idInput);
            }
            idInput.value = prod.product_id;

            const modal = document.getElementById('menuModal');
            const dialog = document.getElementById('menuModalDialog');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                modal.classList.remove('opacity-0');
                dialog.classList.remove('scale-95');
                dialog.classList.add('scale-100');
            }, 10);
        }

        // Menghapus produk resep setelah konfirmasi.
        async function deleteRecipe(id) {
            if (!await showConfirmDialog('Apakah Anda yakin ingin menghapus resep produk ini?')) return;

            try {
                const res = await fetch(`${API_PRODUCT}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    const data = await res.json();
                    throw new Error(data.message || 'Gagal menghapus produk');
                }

                loadProducts();
                await showAlertDialog('Resep produk berhasil dihapus secara permanen.');
            } catch (error) {
                await showAlertDialog(error.message);
            }
        }

        // Menutup modal produk dan resep dengan animasi.
        function closeMenuModal() {
            const modal = document.getElementById('menuModal');
            const dialog = document.getElementById('menuModalDialog');

            // Animasi pop-out
            modal.classList.add('opacity-0');
            dialog.classList.remove('scale-100');
            dialog.classList.add('scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300); // Tunggu animasi selesai
        }

        // Menambahkan satu baris input bahan baku pada form resep.
        function addIngredientRow() {
            const container = document.getElementById('ingredientsContainer');
            const rowId = Date.now();

            // Pilihan Option Bahan
            const options = masterIngredients.map(i => `<option value="${i.ingredient_id}" ${i.status === 'inactive' ? 'disabled' : ''}>${i.name} (${i.unit})${i.status === 'inactive' ? ' - Inaktif' : ''}</option>`).join('');

            const html = `
                                                            <div class="ing-row flex items-center gap-3 bg-slate-50 dark:bg-[#050505] border border-slate-200 dark:border-slate-800 p-3 rounded-xl transition-all" id="row-${rowId}">
                                                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 flex justify-center items-center text-slate-400 shrink-0">
                                                                    ${ingredientIcon}
                                                                </div>
                                                                <div class="flex-1">
                                                                    <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wide mb-1.5 ml-1">Bahan Baku</label>
                                                                    <select class="ing-select w-full bg-white border border-slate-300 rounded-xl px-3 py-2 focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none text-sm font-medium text-slate-700 dark:bg-[#050505] dark:border-slate-800 dark:focus:border-white dark:focus:ring-white dark:text-white" required>
                                                                        <option value="">Pilih Bahan Baku</option>
                                                                        ${options}
                                                                    </select>
                                                                </div>
                                                                <div class="w-24 shrink-0 px-2 border-l border-slate-200 dark:border-slate-800">
                                                                     <label class="block text-center text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wide mb-1.5">Takaran</label>
                                                                     <input type="number" step="0.01" class="ing-qty w-full bg-white border border-slate-300 rounded-xl px-2 py-2 text-center font-bold text-slate-700 dark:text-white focus:border-slate-900 focus:ring-2 focus:ring-slate-900 outline-none text-sm placeholder-slate-300 dark:focus:border-white dark:focus:ring-white" required placeholder="0.0">
                                                                </div>
                                                                <button type="button" onclick="document.getElementById('row-${rowId}').remove()" class="w-10 h-10 p-0 rounded-lg text-slate-400 hover:text-white hover:bg-red-500 dark:hover:bg-red-600 flex justify-center items-center transition-colors shrink-0">
                                                                    ${removeIcon}
                                                                </button>
                                                            </div>
                                                        `;
            container.insertAdjacentHTML('beforeend', html);
        }

        // Mengirim data produk, gambar, dan array bahan baku ke backend.
        document.getElementById('compositeForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            // 1. Kumpulkan Data Tabel Bahan Baku
            const rows = document.querySelectorAll('.ing-row');
            if (rows.length === 0) return await showAlertDialog('Pilih minimal 1 bahan baku resep!');

            const combinedData = new FormData();
            combinedData.append('name', document.getElementById('prodName').value);
            combinedData.append('category_id', document.getElementById('prodCategory').value);
            combinedData.append('price', document.getElementById('prodPrice').value);
            combinedData.append('status', document.getElementById('prodStatus').value);

            const file = document.getElementById('prodImage').files[0];
            if (file) combinedData.append('image', file);

            // Looping semua bahan baku dan suntikkan ke objek FormData untuk dikirim ke PHP Controller
            rows.forEach((row, index) => {
                const idValue = row.querySelector('.ing-select').value;
                const qtyValue = row.querySelector('.ing-qty').value;
                combinedData.append(`ingredients[${index}][id]`, idValue);
                combinedData.append(`ingredients[${index}][quantity]`, qtyValue);
            });

            // 2. Kirim Ke Backend
            try {
                const btn = document.querySelector('button[form="compositeForm"]');
                if (btn) {
                    btn.innerHTML = "Memproses...";
                    btn.disabled = true;
                }

                const prodIdInput = document.getElementById('prodId');
                const isEditing = prodIdInput && prodIdInput.value;
                const targetUrl = isEditing ? `${API_COMPOSITE}/${prodIdInput.value}` : API_COMPOSITE;

                const res = await fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: combinedData
                });

                const data = await res.json();
                if (btn) {
                    btn.innerHTML = `${saveIcon} Simpan Menu`;
                    btn.disabled = false;
                }

                if (res.ok) {
                    await showAlertDialog(isEditing ? 'Data resep dan menu berhasil diperbarui!' : data.message);
                    closeMenuModal();
                    loadProducts(); // Segarkan isi keramik (*grid*)
                } else {
                    await showAlertDialog(data.message || 'Gagal menyimpan menu. Cek kelengkapan bahan.');
                }
            } catch (err) {
                console.error(err);
                await showAlertDialog('Kesalahan jaringan / backend sistem.');
            }
        });
    </script>
@endsection
