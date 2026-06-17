@extends('layouts.admin')

@section('content')
    {{-- Wrapper utama halaman stok bahan --}}
    <div class="w-full">
        {{-- Konten utama halaman stok bahan --}}
        <main class="w-full">
            {{-- Panel informasi cara pencatatan satuan bahan --}}
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800/50 dark:bg-blue-900/20">
                <x-icons.information-circle class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />

                {{-- Isi teks tips pencatatan bahan --}}
                <div>
                    <h4 class="mb-1 text-sm font-bold text-blue-800 dark:text-blue-300">Tips Pencatatan Satuan</h4>
                    <p class="text-xs leading-relaxed text-blue-600/80 dark:text-blue-400/80">
                        Jika Anda memiliki stok belanjaan seberat 5 Kg, dan 1 porsi produk menggunakan 100 Gram, pastikan
                        untuk mencatat stok awal dalam satuan terkecil yaitu <b>5000 Gram</b> agar perhitungan algoritma
                        resep akurat.
                    </p>
                </div>
            </div>

            {{-- Header halaman dan tombol tambah stok --}}
            <div class="mb-6 flex items-start justify-between">
                {{-- Judul dan deskripsi halaman stok bahan --}}
                <div>
                    <h1 class="m-0 text-2xl font-bold leading-tight tracking-tight text-slate-900">
                        Manajemen Bahan Setengah Jadi
                    </h1>
                    <p class="mt-1 text-sm font-medium text-slate-500">Kelola stok bahan setengah jadi di sini.</p>
                </div>

                {{-- Tombol untuk membuka modal tambah bahan --}}
                <button onclick="openModal()"
                    class="rounded-lg bg-blue-500 px-6 py-3 text-sm font-semibold text-white transition-all duration-150 hover:bg-blue-600 active:scale-95">
                    + Tambah Stok
                </button>
            </div>

            {{-- Area filter status bahan --}}
            <div class="mb-6 flex flex-wrap items-end gap-4">
                {{-- Input filter status bahan --}}
                <div>
                    <label for="statusFilter"
                        class="mb-2 flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-slate-500">
                        <x-icons.filter class="h-4 w-4" />
                        Filter Status
                    </label>
                    <select id="statusFilter"
                        class="h-[42px] min-w-[180px] cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-900 outline-none transition-colors focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
                        <option value="all">Semua</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Inaktif</option>
                    </select>
                </div>
            </div>

            {{-- Card tabel daftar bahan --}}
            <div class="mb-8 overflow-hidden rounded-2xl bg-white shadow-md">
                {{-- Wrapper scroll horizontal tabel bahan --}}
                <div class="overflow-x-auto">
                    <table class="w-full table-fixed border-collapse">
                        <thead>
                            {{-- Header kolom tabel bahan --}}
                            <tr class="bg-[#1e2e53] text-left text-xs font-bold uppercase tracking-wider text-white">
                                <th class="w-[35%] px-6 py-5">Nama Item</th>
                                <th class="w-[18%] px-6 py-5">Sisa Stok</th>
                                <th class="w-[17%] px-6 py-5">Satuan</th>
                                <th class="w-[12%] px-6 py-5">Status</th>
                                <th class="w-[18%] px-6 py-5 text-right">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="ingredientsTable">
                            {{-- State awal sebelum data bahan berhasil dimuat --}}
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-sm font-medium text-slate-500">
                                    Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    {{-- Component modal tambah dan edit bahan --}}
    <x-admin.ingredients.ingredient-add-and-edit-modal />

    {{-- Component modal alert dan konfirmasi --}}
    <x-admin.ingredients.alert-and-confirm-modal />
@endsection

@section('scripts')
    <script>
        // State utama halaman stok bahan.
        let ingredients = [];

        // Endpoint API untuk CRUD bahan.
        const API_URL = '/admin/api/ingredients';

        // Icon yang dirender dari Blade component untuk digunakan di template JavaScript.
        const editIcon = @json(view('components.icons.pencil-square', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-4 w-4 align-middle'])])->render());
        const deleteIcon = @json(view('components.icons.trash', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-4 w-4 align-middle'])])->render());
        const warningIcon = @json(view('components.icons.warning-triangle', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-[18px] w-[18px] text-amber-500 align-middle'])])->render());

        // Membuat badge status aktif atau inaktif.
        function statusBadge(status) {
            const isActive = status === 'active';
            const badgeClass = isActive
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-700';

            return `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-extrabold ${badgeClass}">${isActive ? 'Aktif' : 'Inaktif'}</span>`;
        }

        // Mengurutkan bahan aktif sebelum bahan inaktif.
        function sortActiveFirst(items) {
            return [...items].sort((a, b) => (a.status === 'inactive') - (b.status === 'inactive'));
        }

        // Mengubah angka stok menjadi format angka Indonesia.
        function formatQuantity(value) {
            const number = Number(value);
            if (!Number.isFinite(number)) return value ?? '';

            return number.toLocaleString('id-ID', {
                maximumFractionDigits: 2
            });
        }

        // Menyamakan satuan lama dari database dengan value option pada modal.
        function normalizeUnit(unit) {
            const units = {
                gram: 'Gram',
                kg: 'Kg',
                pcs: 'Pcs',
                pack: 'Kantong',
                kantong: 'Kantong',
            };

            return units[String(unit).toLowerCase()] || unit;
        }

        // Menampilkan modal konfirmasi dengan pilihan Tidak dan Ya.
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

                // Tampilkan modal konfirmasi.
                alertModal.classList.remove('hidden');
                alertModal.classList.add('flex');
                void alertModal.offsetWidth;
                alertModal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');

                // Tutup modal konfirmasi dan kirim hasil pilihan user.
                const closeAndResolve = (result) => {
                    alertModal.classList.add('opacity-0');
                    content.classList.remove('scale-100');
                    content.classList.add('scale-95');
                    setTimeout(() => {
                        alertModal.classList.add('hidden');
                        alertModal.classList.remove('flex');
                        resolve(result);
                    }, 200);
                };

                // Event tombol modal konfirmasi.
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

                // Tampilkan modal alert.
                alertModal.classList.remove('hidden');
                alertModal.classList.add('flex');
                void alertModal.offsetWidth;
                alertModal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');

                // Tutup modal alert setelah tombol Tutup diklik.
                const closeAndResolve = () => {
                    alertModal.classList.add('opacity-0');
                    content.classList.remove('scale-100');
                    content.classList.add('scale-95');
                    setTimeout(() => {
                        alertModal.classList.add('hidden');
                        alertModal.classList.remove('flex');
                        resolve(true);
                    }, 200);
                };

                // Event tombol tutup modal alert.
                document.getElementById('modalBtnOk').onclick = () => closeAndResolve();
            });
        }

        // Event awal halaman untuk memuat data bahan.
        document.addEventListener('DOMContentLoaded', loadIngredients);

        // Event filter status untuk merender ulang tabel bahan.
        document.getElementById('statusFilter').addEventListener('change', renderTable);

        // Mengambil daftar bahan dari API.
        async function loadIngredients() {
            try {
                const res = await fetch(API_URL, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                ingredients = sortActiveFirst(data.data);
                renderTable();
            } catch (error) {
                console.error(error);
                await showAlertDialog('Gagal memuat bahan baku');
            }
        }

        // Merender tabel bahan sesuai data dan filter yang aktif.
        function renderTable() {
            const tbody = document.getElementById('ingredientsTable');
            const selectedStatus = document.getElementById('statusFilter').value;
            const filteredIngredients = selectedStatus === 'all'
                ? ingredients
                : ingredients.filter(ing => ing.status === selectedStatus);

            if (filteredIngredients.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <!-- Empty state ketika tidak ada bahan yang sesuai filter -->
                            <div class="flex flex-col items-center justify-center">
                                <x-icons.cube class="mb-3 h-12 w-12 text-slate-300" />
                                <p class="text-sm font-medium">Belum ada bahan. Silakan tambah stok baru.</p>
                            </div>
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = filteredIngredients.map((ing, index) => {
                const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-slate-50';
                const isInactive = ing.status === 'inactive';
                const stockColorClass = ing.stock <= 0
                    ? 'text-red-700'
                    : ing.stock < 100
                        ? 'text-orange-700'
                        : 'text-slate-700';

                return `
                    <tr class="${rowClass} transition-colors">
                        <td class="px-6 py-5 align-middle [overflow-wrap:anywhere]">
                            <!-- Nama bahan dan indikator bahan inaktif -->
                            <div class="flex items-center gap-2 text-sm font-bold text-black">
                                ${ing.name} ${isInactive ? warningIcon : ''}
                            </div>
                        </td>
                        <td class="px-6 py-5 align-middle">
                            <!-- Sisa stok bahan -->
                            <span class="inline-flex rounded-lg border border-slate-200 bg-white px-3 py-1 text-sm font-bold tracking-tight ${stockColorClass}">
                                ${formatQuantity(ing.stock)}
                            </span>
                        </td>
                        <td class="px-6 py-5 align-middle text-sm font-medium text-black">
                            ${normalizeUnit(ing.unit)}
                        </td>
                        <td class="px-6 py-5 align-middle">${statusBadge(ing.status)}</td>
                        <td class="px-6 py-5 align-middle">
                            <!-- Tombol aksi edit dan hapus bahan -->
                            <div class="flex justify-end gap-2">
                                <button class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition-all duration-200 hover:bg-slate-200 active:scale-95" onclick="editIngredient(${ing.ingredient_id})">${editIcon} Edit</button>
                                <button class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-4 py-2 text-xs font-bold text-red-500 transition-all duration-200 hover:bg-red-100 active:scale-95" onclick="deleteIngredient(${ing.ingredient_id})">${deleteIcon} Hapus</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Referensi elemen modal tambah dan edit bahan.
        const modal = document.getElementById('ingredientModal');
        const modalDialog = document.getElementById('modalDialog');
        const form = document.getElementById('ingredientForm');
        const title = document.getElementById('modalTitle');
        const idInput = document.getElementById('ingredientId');
        const nameInput = document.getElementById('ingredientName');
        const stockInput = document.getElementById('ingredientStock');
        const unitInput = document.getElementById('ingredientUnit');
        const statusInput = document.getElementById('ingredientStatus');

        // Membuka modal dalam mode tambah bahan.
        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalDialog.classList.remove('scale-95');
                modalDialog.classList.add('scale-100');
            }, 10);

            title.textContent = 'Tambah Bahan Baku';
            form.reset();
            idInput.value = '';
            statusInput.value = 'active';
        }

        // Menutup modal tambah atau edit bahan.
        function closeModal() {
            modal.classList.add('opacity-0');
            modalDialog.classList.remove('scale-100');
            modalDialog.classList.add('scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        // Membuka modal dalam mode edit dan mengisi form dengan data bahan.
        function editIngredient(id) {
            const ing = ingredients.find(i => i.ingredient_id === id);

            idInput.value = ing.ingredient_id;
            nameInput.value = ing.name;
            stockInput.value = formatQuantity(ing.stock).replace(/\./g, '').replace(',', '.');
            unitInput.value = normalizeUnit(ing.unit);
            statusInput.value = ing.status;
            title.textContent = 'Edit Bahan Baku';

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalDialog.classList.remove('scale-95');
                modalDialog.classList.add('scale-100');
            }, 10);
        }

        // Menangani submit form tambah dan edit bahan.
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = idInput.value;
            const payload = {
                name: nameInput.value,
                stock: stockInput.value,
                unit: unitInput.value,
                status: statusInput.value
            };

            const method = id ? 'PUT' : 'POST';
            const url = id ? `${API_URL}/${id}` : API_URL;

            try {
                // Kirim data bahan ke API sesuai mode tambah atau edit.
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    // Refresh tabel setelah data berhasil disimpan.
                    closeModal();
                    loadIngredients();
                    await showAlertDialog(id ? 'Stok bahan baku diperbarui!' : 'Bahan baku baru berhasil ditambahkan!');
                } else {
                    const data = await res.json();
                    await showAlertDialog(data.message || 'Gagal menyimpan bahan baku');
                }
            } catch (error) {
                console.error(error);
                await showAlertDialog('Terjadi kesalahan pada sistem');
            }
        });

        // Menghapus bahan setelah user menyetujui dialog konfirmasi.
        async function deleteIngredient(id) {
            if (!await showConfirmDialog('Apakah Anda yakin ingin menghapus bahan ini?')) return;

            try {
                // Kirim request hapus bahan ke API.
                const res = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    const data = await res.json();
                    throw new Error(data.message || 'Gagal menghapus');
                }

                // Refresh tabel setelah bahan berhasil dihapus.
                loadIngredients();
            } catch (error) {
                await showAlertDialog(error.message);
            }
        }

        // Menutup modal jika user menekan area overlay di luar form.
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    </script>
@endsection
