@extends('layouts.admin')

@section('content')
    {{-- Header halaman manajemen pengeluaran --}}
    <div class="mb-6">
        {{-- Judul halaman --}}
        <div class="mb-4 flex items-center justify-between">
            <h1 class="m-0 text-2xl font-bold tracking-tight text-slate-900">Manajemen Pengeluaran</h1>
        </div>
    </div>

    {{-- Wrapper utama form dan tabel pengeluaran --}}
    <div class="flex flex-col gap-8">
        {{-- Card form input pengeluaran --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-md dark:border-slate-800 dark:bg-black dark:text-white">
            {{-- Judul form pengeluaran --}}
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-50">Input Pengeluaran Baru</h2>
            </div>

            <form id="expenseForm">
                {{-- Grid input form pengeluaran --}}
                <div class="grid grid-cols-1 items-end gap-6 md:grid-cols-[1fr_2fr_1fr_auto]">
                    {{-- Input kategori pengeluaran --}}
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Kategori</label>
                        <select id="category" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-black dark:text-slate-50">
                            <option value="ingredient">Bahan Baku</option>
                            <option value="operational">Operasional</option>
                            <option value="others">Lain-lain</option>
                        </select>
                    </div>

                    {{-- Input deskripsi pengeluaran --}}
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Deskripsi (Apa yang dibeli)</label>
                        <input type="text" id="description" placeholder="Contoh: Beli Kertas Thermal" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-black dark:text-slate-50">
                    </div>

                    {{-- Input nominal pengeluaran --}}
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Harga (Rupiah)</label>
                        <input type="number" id="amount" placeholder="0" min="1" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-black dark:text-slate-50">
                    </div>

                    {{-- Tombol submit form pengeluaran --}}
                    <button type="submit"
                        class="h-[50px] w-full rounded-lg bg-emerald-500 px-6 text-sm font-semibold text-white transition-colors duration-300 ease-in-out hover:bg-emerald-600 md:w-auto">
                        Kirim
                    </button>
                </div>
            </form>
        </div>

        {{-- Area riwayat pengeluaran --}}
        <div class="flex flex-col gap-4">
            {{-- Header riwayat dan total pengeluaran --}}
            <div class="flex items-end justify-between">
                <h2 class="m-0 text-xl font-bold tracking-tight text-slate-900">Riwayat Pengeluaran</h2>
                <div class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Total:
                    <span id="totalExpense" class="text-xl font-extrabold tracking-tight text-red-500 dark:text-red-400">Rp 0</span>
                </div>
            </div>

            {{-- Kontrol filter pengeluaran --}}
            <div class="flex flex-wrap items-end gap-4">
                {{-- Filter cepat berdasarkan waktu --}}
                <div class="flex flex-col items-start gap-2">
                    <label for="timeFilter" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <x-icons.filter class="h-4 w-4" />
                        Filter Pengeluaran
                    </label>
                    <select id="timeFilter"
                        class="h-[38px] min-w-[180px] cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-900 outline-none transition-colors focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
                        <option value="today">Hari Ini</option>
                        <option value="all">Semua Pengeluaran</option>
                        <option value="custom">Filter Tanggal</option>
                    </select>
                </div>

                {{-- Filter tanggal custom, hanya tampil saat opsi Filter Tanggal dipilih --}}
                <div id="customDateContainer" class="hidden items-end gap-2">
                    {{-- Input tanggal mulai --}}
                    <div>
                        <label for="startDate" class="mb-2 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <x-icons.calendar-days class="h-4 w-4" />
                            Mulai
                        </label>
                        <input type="date" id="startDate"
                            class="h-[38px] rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-900 outline-none transition-colors focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
                    </div>

                    {{-- Input tanggal sampai --}}
                    <div>
                        <label for="endDate" class="mb-2 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <x-icons.calendar-days class="h-4 w-4" />
                            Sampai
                        </label>
                        <input type="date" id="endDate"
                            class="h-[38px] rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-900 outline-none transition-colors focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
                    </div>

                    {{-- Tombol untuk menerapkan filter tanggal custom --}}
                    <div>
                        <button id="btnApplyFilter"
                            class="inline-flex h-[38px] items-center gap-2 rounded-lg bg-emerald-500 px-4 text-sm font-semibold text-white transition-colors duration-300 ease-in-out hover:bg-emerald-600">
                            <x-icons.check-circle class="h-4 w-4" />
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>

            {{-- Card tabel riwayat pengeluaran --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md dark:border-slate-800 dark:bg-black">
                {{-- Wrapper scroll horizontal tabel pengeluaran --}}
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            {{-- Header kolom tabel pengeluaran --}}
                            <tr>
                                <th class="border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-white dark:border-slate-700">Tanggal & Waktu</th>
                                <th class="border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-white dark:border-slate-700">Kategori</th>
                                <th class="border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-white dark:border-slate-700">Deskripsi</th>
                                <th class="border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-right text-xs font-bold uppercase tracking-wider text-white dark:border-slate-700">Jumlah</th>
                                <th class="border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-right text-xs font-bold uppercase tracking-wider text-white dark:border-slate-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="expensesTable">
                            {{-- State awal saat data pengeluaran sedang dimuat --}}
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-sm font-medium text-slate-500">
                                    Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Component modal alert dan konfirmasi --}}
    <x-admin.expenses.alert-and-confirm-modal />
@endsection

@section('scripts')
    <script>
        // Endpoint API untuk CRUD pengeluaran.
        const API_URL = '/admin/api/expenses';

        // Icon dari Blade component untuk tombol aksi dan modal konfirmasi.
        const editIcon = @json(view('components.icons.pencil-square', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-4 w-4'])])->render());
        const deleteIcon = @json(view('components.icons.trash', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-4 w-4'])])->render());
        const emptyExpenseIcon = @json(view('components.icons.trending-down', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-8 w-8 text-red-400'])])->render());

        // Menyimpan seluruh data pengeluaran dari API sebelum difilter.
        let allExpenses = [];

        // State mode edit form pengeluaran.
        let isEditing = false;
        let editingId = null;

        // Label kategori yang ditampilkan pada tabel.
        const expenseCategoryLabels = {
            ingredient: 'Bahan Baku',
            operational: 'Operasional',
            others: 'Lain-lain'
        };

        // Mengubah kode kategori menjadi label yang mudah dibaca.
        function formatCategory(category) {
            return expenseCategoryLabels[category] ?? category ?? '-';
        }

        // Mengubah angka menjadi format mata uang Rupiah.
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        // Mengubah tanggal database menjadi format tanggal dan jam Indonesia.
        const formatDate = (dateString) => {
            const dateObj = new Date(dateString);
            const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            const timeStr = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            return `
                <!-- Tanggal dan jam pengeluaran -->
                <div class="text-sm font-medium text-slate-700">${dateStr}</div>
                <div class="text-xs text-slate-400">${timeStr} WIB</div>
            `;
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

                alertModal.classList.remove('hidden');
                alertModal.classList.add('flex');
                void alertModal.offsetWidth;
                alertModal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');

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
                void alertModal.offsetWidth;
                alertModal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');

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

                document.getElementById('modalBtnOk').onclick = () => closeAndResolve();
            });
        }

        // Mengambil daftar pengeluaran dari API.
        async function loadExpenses() {
            try {
                const res = await fetch(API_URL, {
                    headers: { 'Accept': 'application/json' }
                });
                const responseData = await res.json();
                allExpenses = responseData.data;

                renderTable();
            } catch (error) {
                console.error('Error:', error);
                await showAlertDialog('Gagal memuat data pengeluaran.');
            }
        }

        // Merender data pengeluaran ke tabel sesuai filter yang sedang aktif.
        function renderTable() {
            const tbody = document.getElementById('expensesTable');
            const filterVal = document.getElementById('timeFilter').value;

            let filteredExpenses = allExpenses;

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (filterVal === 'today') {
                // Filter pengeluaran yang dibuat pada tanggal hari ini.
                filteredExpenses = allExpenses.filter(expense => {
                    const expenseDate = new Date(expense.created_at);
                    expenseDate.setHours(0, 0, 0, 0);

                    return expenseDate.getTime() === today.getTime();
                });
            } else if (filterVal === 'custom') {
                // Filter pengeluaran berdasarkan rentang tanggal yang dipilih user.
                const startVal = document.getElementById('startDate').value;
                const endVal = document.getElementById('endDate').value;

                if (startVal && endVal) {
                    const startDate = new Date(startVal);
                    startDate.setHours(0, 0, 0, 0);

                    const endDate = new Date(endVal);
                    endDate.setHours(23, 59, 59, 999);

                    filteredExpenses = allExpenses.filter(expense => {
                        const expenseDate = new Date(expense.created_at);

                        return expenseDate >= startDate && expenseDate <= endDate;
                    });
                }
            }

            calculateTotal(filteredExpenses);

            if (filteredExpenses.length === 0) {
                // Render empty state jika tidak ada pengeluaran yang sesuai filter.
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <!-- Ikon kosong dan pesan data pengeluaran tidak ditemukan -->
                            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-50">
                                ${emptyExpenseIcon}
                            </div>
                            <h3 class="mb-2 text-sm font-bold text-slate-900">Tidak Ada Pengeluaran</h3>
                            <p class="text-sm text-slate-500">Tidak ada data pengeluaran yang sesuai dengan filter Anda.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = filteredExpenses.map((item, index) => {
                const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-slate-50';
                const escapedDescription = String(item.description ?? '').replace(/'/g, "\\'");

                return `
                    <tr class="${rowClass}">
                        <td class="border-b border-slate-100 px-6 py-5 align-middle text-slate-700">${formatDate(item.created_at)}</td>
                        <td class="border-b border-slate-100 px-6 py-5 align-middle text-sm font-bold text-slate-700">${formatCategory(item.category)}</td>
                        <td class="border-b border-slate-100 px-6 py-5 align-middle text-sm font-bold text-black">${item.description}</td>
                        <td class="border-b border-slate-100 px-6 py-5 text-right align-middle text-sm font-bold text-slate-900">${formatRupiah(item.amount)}</td>
                        <td class="border-b border-slate-100 px-6 py-5 text-right align-middle">
                            <!-- Tombol aksi edit dan hapus pengeluaran -->
                            <button onclick="editExpense(${item.expense_id}, '${item.category}', '${escapedDescription}', ${item.amount})" class="inline-flex items-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition-all hover:bg-slate-200" title="Ubah">${editIcon}</button>
                            <button onclick="deleteExpense(${item.expense_id})" class="inline-flex items-center rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-500 transition-all hover:bg-red-200" title="Hapus">${deleteIcon}</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Menghitung total seluruh pengeluaran yang sedang dimuat.
        function calculateTotal(expenses) {
            const total = expenses.reduce((sum, item) => sum + parseInt(item.amount), 0);
            document.getElementById('totalExpense').textContent = formatRupiah(total);
        }

        // Menangani submit form tambah dan edit pengeluaran.
        document.getElementById('expenseForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const category = document.getElementById('category').value;
            const description = document.getElementById('description').value;
            const amount = document.getElementById('amount').value;
            const btn = e.target.querySelector('button');

            btn.disabled = true;
            btn.textContent = 'Mengirim...';

            try {
                const url = isEditing ? `${API_URL}/${editingId}` : API_URL;
                const method = isEditing ? 'PUT' : 'POST';

                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ category, description, amount })
                });

                const data = await res.json();

                if (res.ok) {
                    document.getElementById('expenseForm').reset();
                    loadExpenses();
                } else {
                    await showAlertDialog(data.message || 'Gagal menyimpan data');
                }
            } catch (error) {
                console.error(error);
                await showAlertDialog('Terjadi kesalahan jaringan');
            } finally {
                btn.disabled = false;
                btn.textContent = isEditing ? 'Kirim' : 'Kirim';
                isEditing = false;
                editingId = null;
            }
        });

        // Mengisi form dengan data pengeluaran yang akan diedit.
        window.editExpense = function (id, category, description, amount) {
            document.getElementById('category').value = category;
            document.getElementById('description').value = description;
            document.getElementById('amount').value = amount;
            isEditing = true;
            editingId = id;

            const btn = document.querySelector('#expenseForm button[type="submit"]');
            btn.textContent = 'Update';

            document.getElementById('expenseForm').scrollIntoView({ behavior: 'smooth' });
        };

        // Menghapus pengeluaran setelah user menyetujui konfirmasi.
        window.deleteExpense = async function (id) {
            if (!await showConfirmDialog('Hapus data pengeluaran ini?')) return;

            try {
                const res = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    loadExpenses();
                } else {
                    await showAlertDialog('Gagal menghapus data');
                }
            } catch (error) {
                console.error(error);
                await showAlertDialog('Terjadi kesalahan');
            }
        }

        // Memuat data dan memasang event filter saat halaman selesai dibuka.
        document.addEventListener('DOMContentLoaded', () => {
            loadExpenses();

            // Menampilkan atau menyembunyikan input tanggal custom sesuai pilihan filter.
            document.getElementById('timeFilter').addEventListener('change', function () {
                const customDateContainer = document.getElementById('customDateContainer');

                if (this.value === 'custom') {
                    customDateContainer.classList.remove('hidden');
                    customDateContainer.classList.add('flex');
                } else {
                    customDateContainer.classList.add('hidden');
                    customDateContainer.classList.remove('flex');
                    renderTable();
                }
            });

            // Menerapkan filter tanggal custom.
            document.getElementById('btnApplyFilter').addEventListener('click', () => {
                renderTable();
            });
        });
    </script>
@endsection
