@extends('layouts.admin')

@section('content')
    {{-- Header halaman manajemen akun --}}
    <div class="mb-4 flex items-center justify-between">
        <h1 class="m-0 text-2xl font-bold tracking-tight text-slate-900">Manajemen Akun</h1>
        <button type="button" onclick="openModal()"
            class="rounded-lg bg-[#007bff] px-6 py-3 text-sm font-semibold text-white transition hover:opacity-80 active:scale-95">
            + Tambah Akun
        </button>
    </div>

    {{-- Area filter status akun --}}
    <div class="mb-6 flex flex-wrap items-end gap-4">
        {{-- Input filter status akun --}}
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
    </div>

    <h2 class="mb-4 text-lg font-bold text-slate-900">Akun Admin</h2>
    {{-- Card tabel akun admin --}}
    <div class="mb-8 overflow-hidden rounded-2xl bg-white shadow-md">
        {{-- Wrapper scroll horizontal tabel admin --}}
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse">
                <thead>
                    <tr class="bg-[#1e2e53] text-left text-white">
                        <th class="w-[22%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Nama</th>
                        <th class="w-[24%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Email</th>
                        <th class="w-[14%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Dibuat Pada</th>
                        <th class="w-[10%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Role</th>
                        <th class="w-[10%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Status</th>
                        <th class="w-[20%] px-6 py-5 text-right text-xs font-bold uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody id="adminTableBody">
                    {{-- Data loaded via JavaScript --}}
                </tbody>
            </table>
        </div>
    </div>

    <h2 class="mb-4 text-lg font-bold text-slate-900">Akun Kasir</h2>
    {{-- Card tabel akun kasir --}}
    <div class="mb-8 overflow-hidden rounded-2xl bg-white shadow-md">
        {{-- Wrapper scroll horizontal tabel kasir --}}
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse">
                <thead>
                    <tr class="bg-[#1e2e53] text-left text-white">
                        <th class="w-[22%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Nama</th>
                        <th class="w-[24%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Email</th>
                        <th class="w-[14%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Dibuat Pada</th>
                        <th class="w-[10%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Role</th>
                        <th class="w-[10%] px-6 py-5 text-xs font-bold uppercase tracking-wide">Status</th>
                        <th class="w-[20%] px-6 py-5 text-right text-xs font-bold uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody id="cashierTableBody">
                    {{-- Data loaded via JavaScript --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- Component modal tambah dan edit akun --}}
    <x-admin.users.user-modal />

    {{-- Component modal alert dan konfirmasi --}}
    <x-admin.users.alert-modal />

    {{-- Icon tersembunyi untuk digunakan ulang oleh JavaScript --}}
    <div class="hidden">
        <span id="iconEdit"><x-icons.pencil-square class="h-4 w-4" /></span>
        <span id="iconDelete"><x-icons.trash class="h-4 w-4" /></span>
        <span id="iconCancel"><x-icons.x-mark class="h-[18px] w-[18px]" /></span>
        <span id="iconConfirm"><x-icons.check-circle class="h-[18px] w-[18px]" /></span>
    </div>
@endsection

@section('scripts')
    <script>
        const API_URL = '/admin/api/users';
        const currentUserId = @json(auth()->id());
        const icons = {
            edit: document.getElementById('iconEdit').innerHTML,
            delete: document.getElementById('iconDelete').innerHTML,
            cancel: document.getElementById('iconCancel').innerHTML,
            confirm: document.getElementById('iconConfirm').innerHTML,
        };

        let users = [];
        let isEditing = false;

        const modal = document.getElementById('userModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('userForm');

        // Membersihkan teks sebelum dimasukkan ke HTML.
        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Mengubah tanggal database menjadi format tanggal Indonesia.
        function formatDate(value) {
            if (!value) return '-';

            return new Date(value).toLocaleDateString('id-ID');
        }

        // Membuat badge status akun aktif atau inaktif.
        function statusBadge(status) {
            const isActive = status === 'active';
            const classes = isActive
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-700';

            return `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-extrabold ${classes}">${isActive ? 'Aktif' : 'Inaktif'}</span>`;
        }

        // Membuat badge role admin atau kasir.
        function roleBadge(role) {
            const classes = role === 'admin'
                ? 'bg-indigo-100 text-indigo-700'
                : 'bg-green-100 text-green-800';

            return `<span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide ${classes}">${escapeHtml(role)}</span>`;
        }

        // Mengurutkan akun aktif agar tampil lebih dahulu.
        function sortActiveFirst(items) {
            return [...items].sort((a, b) => (a.status === 'inactive') - (b.status === 'inactive'));
        }

        // Membuat baris kosong ketika data akun tidak tersedia.
        function renderEmptyRow(message) {
            return `<tr><td colspan="6" class="px-4 py-4 text-center text-sm font-medium text-slate-500">${message}</td></tr>`;
        }

        // Merender tabel akun admin dan kasir sesuai filter status.
        function renderUsers() {
            const selectedStatus = document.getElementById('statusFilter').value;
            const filteredUsers = selectedStatus === 'all'
                ? users
                : users.filter(user => user.status === selectedStatus);

            const admins = filteredUsers.filter(user => user.role === 'admin');
            const cashiers = filteredUsers.filter(user => user.role === 'cashier');

            // Membuat satu baris tabel akun.
            const renderRow = (user, index) => {
                const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-slate-50';

                return `
                    <tr class="${rowClass}">
                        <td class="break-words px-6 py-5">
                            <div class="text-sm font-bold text-black">${escapeHtml(user.name)}</div>
                        </td>
                        <td class="break-words px-6 py-5 text-sm font-medium text-black">${escapeHtml(user.email)}</td>
                        <td class="px-6 py-5 text-sm font-medium text-black">${formatDate(user.created_at)}</td>
                        <td class="px-6 py-5">${roleBadge(user.role)}</td>
                        <td class="px-6 py-5">${statusBadge(user.status)}</td>
                        <td class="px-6 py-5 text-right">
                            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:opacity-80 active:scale-95" onclick="editUser(${Number(user.user_id)})">
                                ${icons.edit}
                                Edit
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-red-100 px-4 py-2.5 text-xs font-bold text-red-500 transition hover:opacity-80 active:scale-95" onclick="deleteUser(${Number(user.user_id)})">
                                ${icons.delete}
                                Hapus
                            </button>
                        </td>
                    </tr>
                `;
            };

            document.getElementById('adminTableBody').innerHTML =
                admins.map(renderRow).join('') || renderEmptyRow('Tidak ada data admin');

            document.getElementById('cashierTableBody').innerHTML =
                cashiers.map(renderRow).join('') || renderEmptyRow('Tidak ada data kasir');
        }

        // Membuka modal alert atau konfirmasi.
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

        // Menutup modal alert dan mengembalikan hasil pilihan user.
        function closeAlertModal(resolve, result) {
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

        // Menampilkan modal konfirmasi sebelum aksi penting dijalankan.
        function showConfirmDialog(message) {
            return new Promise((resolve) => {
                const alertButtons = document.getElementById('alertButtons');
                document.getElementById('alertMessage').textContent = message;

                alertButtons.innerHTML = `
                    <button type="button" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-500 px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-80 active:scale-95" id="modalBtnTidak">
                        ${icons.cancel}
                        Tidak
                    </button>
                    <button type="button" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-green-500 px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-80 active:scale-95" id="modalBtnYa">
                        ${icons.confirm}
                        Ya
                    </button>
                `;

                openAlertModal();
                document.getElementById('modalBtnTidak').onclick = () => closeAlertModal(resolve, false);
                document.getElementById('modalBtnYa').onclick = () => closeAlertModal(resolve, true);
            });
        }

        // Menampilkan modal alert satu tombol untuk pesan sistem.
        function showAlertDialog(message) {
            return new Promise((resolve) => {
                const alertButtons = document.getElementById('alertButtons');
                document.getElementById('alertMessage').textContent = message;

                alertButtons.innerHTML = `
                    <button type="button" class="inline-flex w-full items-center justify-center rounded-lg bg-[#007bff] px-8 py-2 text-sm font-semibold text-white transition hover:opacity-80 active:scale-95" id="modalBtnOk">
                        Tutup
                    </button>
                `;

                openAlertModal();
                document.getElementById('modalBtnOk').onclick = () => closeAlertModal(resolve, true);
            });
        }

        // Mengambil pesan error dari response API.
        async function parseErrorMessage(response) {
            const data = await response.json().catch(() => ({}));

            if (data.errors) {
                const firstError = Object.values(data.errors).flat()[0];
                if (firstError) return firstError;
            }

            return data.message || 'Terjadi kesalahan pada server';
        }

        // Mengambil daftar akun dari API.
        async function loadUsers() {
            try {
                const response = await fetch(API_URL);

                if (!response.ok) {
                    throw new Error(await parseErrorMessage(response));
                }

                users = sortActiveFirst(await response.json());
                renderUsers();
            } catch (error) {
                console.error('Error loading users:', error);
                await showAlertDialog('Gagal memuat data pengguna!');
            }
        }

        // Membuka modal tambah akun.
        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modalTitle.textContent = 'Tambah Akun';
            form.reset();

            document.getElementById('userId').value = '';
            document.querySelector('#status option[value="inactive"]').disabled = false;
            document.getElementById('password').required = true;
            document.getElementById('password_confirmation').required = true;
            isEditing = false;
        }

        // Menutup modal akun dan mengosongkan form.
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            form.reset();
        }

        // Mengisi form modal dengan data akun yang akan diedit.
        window.editUser = function (id) {
            const user = users.find(item => Number(item.user_id) === Number(id));
            if (!user) return;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modalTitle.textContent = 'Edit Akun';

            document.getElementById('userId').value = user.user_id;
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('status').value = user.status;

            const inactiveOption = document.querySelector('#status option[value="inactive"]');
            inactiveOption.disabled = Number(user.user_id) === Number(currentUserId);

            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';
            document.getElementById('password').required = false;
            document.getElementById('password_confirmation').required = false;
            isEditing = true;
        }

        // Menangani submit form tambah dan edit akun.
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const id = document.getElementById('userId').value;
            const password = document.getElementById('password').value.trim();
            const passwordConfirmation = document.getElementById('password_confirmation').value.trim();
            const selectedStatus = document.getElementById('status').value;

            if (isEditing && Number(id) === Number(currentUserId) && selectedStatus === 'inactive') {
                await showAlertDialog('Anda tidak dapat menonaktifkan akun yang sedang Anda gunakan saat ini.');
                return;
            }

            const formData = {
                name: document.getElementById('name').value.trim(),
                email: document.getElementById('email').value.trim(),
                role: document.getElementById('role').value,
                status: selectedStatus,
            };

            if (!isEditing || password) {
                formData.password = password;
                formData.password_confirmation = passwordConfirmation;
            }

            const url = isEditing ? `${API_URL}/${id}` : API_URL;
            const method = isEditing ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(formData),
                });

                if (!response.ok) {
                    throw new Error(await parseErrorMessage(response));
                }

                closeModal();
                await loadUsers();
                await showAlertDialog(isEditing ? 'Akun berhasil diperbarui' : 'Akun berhasil ditambahkan');
            } catch (error) {
                await showAlertDialog(error.message);
            }
        });

        // Menghapus akun setelah user menyetujui konfirmasi.
        window.deleteUser = async function (id) {
            if (!await showConfirmDialog('Apakah Anda yakin ingin menghapus akun ini?')) return;

            try {
                const response = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error(await parseErrorMessage(response));
                }

                await loadUsers();
            } catch (error) {
                await showAlertDialog(error.message);
            }
        }

        document.getElementById('statusFilter').addEventListener('change', renderUsers);
        loadUsers();
    </script>
@endsection
