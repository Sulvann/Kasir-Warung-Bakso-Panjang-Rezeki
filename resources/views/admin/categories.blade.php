@extends('layouts.admin')

@section('content')
    {{-- Header halaman manajemen kategori --}}
    <div class="mb-4 flex items-center justify-between">
        <h1 class="m-0 text-2xl font-bold tracking-tight text-slate-900">Manajemen Kategori</h1>
        <button onclick="openModal()"
            class="cursor-pointer rounded-lg bg-blue-500 px-6 py-3 text-sm font-semibold text-white transition-transform duration-150 ease-in-out active:scale-95">
            + Tambah Kategori
        </button>
    </div>

    {{-- Area filter status kategori --}}
    <div class="mb-6 flex flex-wrap items-end gap-4">
        {{-- Input filter status kategori --}}
        <div>
            <label for="statusFilter" class="mb-2 flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-500">
                <x-icons.filter class="h-4 w-4" />
                Filter Status
            </label>
            <select id="statusFilter"
                class="h-[42px] min-w-[180px] cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-900 outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
                <option value="all">Semua</option>
                <option value="active">Aktif</option>
                <option value="inactive">Inaktif</option>
            </select>
        </div>
    </div>

    {{-- Card tabel daftar kategori --}}
    <div class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
        {{-- Wrapper scroll horizontal tabel kategori --}}
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse">
                <thead>
                    <tr class="bg-[#1e2e53] text-left text-white">
                        <th class="w-[60%] px-6 py-5 text-xs font-bold uppercase tracking-wider">Nama</th>
                        <th class="w-[15%] px-6 py-5 text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="w-[25%] px-6 py-5 text-right text-xs font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="categoriesTable">
                    <tr>
                        <td colspan="3" class="px-4 py-4 text-center text-sm font-medium text-slate-500">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <x-admin.categories.categories-add-edit />

    {{-- Modal alert dan konfirmasi kategori --}}
    <div id="alertModal"
        class="fixed inset-0 z-[2000] hidden items-center justify-center bg-slate-900/60 opacity-0 backdrop-blur-sm transition-opacity duration-200">
        {{-- Kotak isi modal alert dan konfirmasi --}}
        <div id="alertModalContent"
            class="w-[350px] max-w-[90%] scale-95 rounded-2xl bg-white p-8 text-center transition-transform duration-200">
            <h3 id="alertMessage" class="mb-8 text-lg font-medium text-slate-900"></h3>

            {{-- Area tombol modal yang diisi oleh JavaScript --}}
            <div id="alertButtons" class="flex justify-center gap-4">
                <!-- Buttons injected via JS -->
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let categories = [];
        const API_URL = '/admin/api/categories';
        const editIcon = @json(view('components.icons.pencil-square', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-4 w-4'])])->render());
        const deleteIcon = @json(view('components.icons.trash', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-4 w-4'])])->render());
        const xMarkIcon = @json(view('components.icons.x-mark', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-[18px] w-[18px]'])])->render());
        const checkCircleIcon = @json(view('components.icons.check-circle', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-[18px] w-[18px]'])])->render());
        // const TOKEN = localStorage.getItem('token'); // Not used

        // Membuat badge status kategori.
        function statusBadge(status) {
            const isActive = status === 'active';

            return `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-extrabold ${isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'}">${isActive ? 'Aktif' : 'Inaktif'}</span>`;
        }

        // Mengurutkan kategori aktif agar tampil lebih dahulu.
        function sortActiveFirst(items) {
            return [...items].sort((a, b) => (a.status === 'inactive') - (b.status === 'inactive'));
        }

        // Menampilkan modal konfirmasi sebelum aksi penting dijalankan.
        function showConfirmDialog(message) {
            return new Promise((resolve) => {
                const modal = document.getElementById('alertModal');
                const content = document.getElementById('alertModalContent');
                const alertButtons = document.getElementById('alertButtons');

                document.getElementById('alertMessage').textContent = message;

                alertButtons.innerHTML = `
                    <button type="button" class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-500 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-600" id="modalBtnTidak">
                        ${xMarkIcon}
                        Tidak
                    </button>
                    <button type="button" class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-green-500 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-green-600" id="modalBtnYa">
                        ${checkCircleIcon}
                        Ya
                    </button>
                `;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                void modal.offsetWidth;
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');

                // Menutup modal konfirmasi dan mengembalikan hasil pilihan user.
                const closeAndResolve = (result) => {
                    modal.classList.add('opacity-0');
                    content.classList.remove('scale-100');
                    content.classList.add('scale-95');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        resolve(result);
                    }, 200);
                };

                document.getElementById('modalBtnTidak').onclick = () => closeAndResolve(false);
                document.getElementById('modalBtnYa').onclick = () => closeAndResolve(true);
            });
        }

        // Menampilkan modal alert satu tombol untuk pesan sistem.
        function showAlertDialog(message) {
            return new Promise((resolve) => {
                const modal = document.getElementById('alertModal');
                const content = document.getElementById('alertModalContent');
                const alertButtons = document.getElementById('alertButtons');

                document.getElementById('alertMessage').textContent = message;

                alertButtons.innerHTML = `
                    <button type="button" class="w-full rounded-lg bg-blue-500 px-8 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-600" id="modalBtnOk">
                        Tutup
                    </button>
                `;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                void modal.offsetWidth;
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');

                // Menutup modal alert setelah user menekan tombol tutup.
                const closeAndResolve = () => {
                    modal.classList.add('opacity-0');
                    content.classList.remove('scale-100');
                    content.classList.add('scale-95');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        resolve(true);
                    }, 200);
                };

                document.getElementById('modalBtnOk').onclick = () => closeAndResolve();
            });
        }

        document.addEventListener('DOMContentLoaded', loadCategories);
        document.getElementById('statusFilter').addEventListener('change', renderTable);

        // Mengambil data kategori dari API.
        async function loadCategories() {
            try {
                const res = await fetch(API_URL, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                categories = sortActiveFirst(data.data);
                renderTable();
            } catch (error) {
                console.error(error);
                await showAlertDialog('Gagal memuat kategori');
            }
        }

        // Merender tabel kategori sesuai filter status yang dipilih.
        function renderTable() {
            const tbody = document.getElementById('categoriesTable');
            const selectedStatus = document.getElementById('statusFilter').value;
            const filteredCategories = selectedStatus === 'all'
                ? categories
                : categories.filter(cat => cat.status === selectedStatus);

            if (filteredCategories.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-center text-sm font-medium text-slate-500">Tidak ada data kategori</td></tr>';
                return;
            }

            tbody.innerHTML = filteredCategories.map((cat, index) => {
                const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-slate-50';

                return `
                    <tr class="${rowClass}">
                        <td class="break-words px-6 py-5 align-middle">
                            <div class="text-sm font-bold text-black">${cat.name}</div>
                        </td>
                        <td class="px-6 py-5 align-middle">${statusBadge(cat.status)}</td>
                        <td class="px-6 py-5 text-right align-middle">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition-all hover:bg-slate-200 active:scale-95" onclick="editCategory(${cat.category_id})">${editIcon} Edit</button>
                            <button class="inline-flex items-center gap-2 rounded-lg bg-red-100 px-4 py-2 text-xs font-bold text-red-500 transition-all hover:bg-red-200 active:scale-95" onclick="deleteCategory(${cat.category_id})">${deleteIcon} Hapus</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Modal Logic
        const modal = document.getElementById('categoryModal');
        const form = document.getElementById('categoryForm');
        const title = document.getElementById('modalTitle');
        const idInput = document.getElementById('categoryId');
        const nameInput = document.getElementById('categoryName');
        const statusInput = document.getElementById('categoryStatus');

        // Membuka modal tambah atau edit kategori.
        function openModal(isEdit = false) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (!isEdit) {
                title.textContent = 'Tambah Kategori';
                form.reset();
                idInput.value = '';
                statusInput.value = 'active';
            }
        }

        // Menutup modal kategori.
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Mengisi form modal dengan data kategori yang akan diedit.
        function editCategory(id) {
            const cat = categories.find(c => c.category_id === id);
            idInput.value = cat.category_id;
            nameInput.value = cat.name;
            statusInput.value = cat.status;
            title.textContent = 'Edit Kategori';
            openModal(true);
        }

        // Menangani submit form tambah dan edit kategori.
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = idInput.value;
            const name = nameInput.value;
            const status = statusInput.value;
            const method = id ? 'PUT' : 'POST';
            const url = id ? `${API_URL}/${id}` : API_URL;

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, status })
                });

                if (res.ok) {
                    closeModal();
                    loadCategories();
                    await showAlertDialog(id ? 'Kategori berhasil diperbarui' : 'Kategori berhasil ditambahkan');
                } else {
                    const data = await res.json();
                    await showAlertDialog(data.message || 'Gagal menyimpan kategori');
                }
            } catch (error) {
                console.error(error);
                await showAlertDialog('Terjadi kesalahan sistem');
            }
        });

        // Menghapus kategori setelah user menyetujui konfirmasi.
        async function deleteCategory(id) {
            if (!await showConfirmDialog('Apakah Anda yakin ingin menghapus kategori ini?')) return;

            try {
                const res = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    const data = await res.json();
                    throw new Error(data.message || 'Gagal menghapus kategori');
                }

                loadCategories();
            } catch (error) {
                await showAlertDialog(error.message);
            }
        }

        // Menutup modal kategori saat user menekan area overlay.
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    </script>
@endsection
