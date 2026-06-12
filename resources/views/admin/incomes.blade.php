@extends('layouts.admin')

@section('content')
    {{-- Wrapper utama halaman riwayat pemasukan --}}
    <div>
        {{-- Header dan area filter pemasukan --}}
        <div class="mb-6">
            {{-- Judul halaman --}}
            <div class="mb-4 flex items-center justify-between">
                <h1 class="m-0 text-2xl font-bold tracking-tight text-slate-900">Riwayat Pemasukan</h1>
            </div>

            {{-- Kontrol filter transaksi --}}
            <div class="flex flex-wrap items-end gap-4">
                {{-- Filter cepat berdasarkan waktu --}}
                <div class="flex flex-col items-start gap-2">
                    <label for="timeFilter" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <x-icons.filter class="h-4 w-4" />
                        Filter Pemasukan
                    </label>
                    <select id="timeFilter"
                        class="h-[38px] min-w-[180px] cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-900 outline-none transition-colors focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
                        <option value="today">Hari Ini</option>
                        <option value="all">Semua Pemasukan</option>
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
        </div>

        {{-- Card tabel transaksi pemasukan --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-md">
            {{-- Wrapper scroll horizontal tabel pada layar kecil --}}
            <div class="overflow-x-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead>
                        {{-- Header kolom tabel transaksi --}}
                        <tr>
                            <th class="w-[20%] border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-white">Nama Pelanggan</th>
                            <th class="w-[20%] border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-white">Waktu Transaksi</th>
                            <th class="w-[20%] border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-white">Nominal Masuk</th>
                            <th class="w-[15%] border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-white">Metode</th>
                            <th class="w-[15%] border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-white">Status</th>
                            <th class="w-[10%] border-b border-slate-200 bg-[#1e2e53] px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-white">Struk</th>
                        </tr>
                    </thead>

                    <tbody id="transactionsTableBody">
                        {{-- State awal saat data transaksi sedang dimuat --}}
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                {{-- Isi loading state tabel transaksi --}}
                                <div class="flex w-full flex-col items-center justify-center gap-4">
                                    <x-icons.spinner />
                                    <span>Sedang memuat data transaksi...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Component modal preview struk pemasukan --}}
    <x-admin.incomes.income-struk-modal />
@endsection

@section('scripts')
    <script>
        // Menyimpan seluruh data transaksi dari API sebelum difilter.
        let allTransactions = [];

        // Menyimpan ID transaksi yang sedang tampil di modal struk.
        let currentStrukTransactionId = null;

        // Icon dari Blade component yang digunakan ulang di template JavaScript.
        const eyeIcon = @json(view('components.icons.eye', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-4 w-4'])])->render());
        const emptyIncomeIcon = @json(view('components.icons.trending-up', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-8 w-8 text-emerald-500'])])->render());
        const whatsappIcon = @json(view('components.icons.chat-bubble-left-right', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-4 w-4'])])->render());

        // Menjalankan proses awal halaman setelah DOM selesai dimuat.
        document.addEventListener('DOMContentLoaded', () => {
            loadTransactions();

            // Menampilkan atau menyembunyikan input tanggal custom sesuai pilihan filter.
            document.getElementById('timeFilter').addEventListener('change', function () {
                const customDateContainer = document.getElementById('customDateContainer');

                if (this.value === 'custom') {
                    customDateContainer.classList.remove('hidden');
                    customDateContainer.classList.add('flex');
                } else {
                    customDateContainer.classList.add('hidden');
                    customDateContainer.classList.remove('flex');
                    renderTransactions();
                }
            });

            // Menerapkan filter tanggal custom.
            document.getElementById('btnApplyFilter').addEventListener('click', () => {
                renderTransactions();
            });
        });

        // Mengambil data transaksi dari API kasir.
        async function loadTransactions() {
            try {
                const res = await fetch('/cashier-api/transactions', {
                    headers: { 'Accept': 'application/json' }
                });
                const result = await res.json();

                if (result.status === 'success') {
                    allTransactions = result.data;
                    renderTransactions();
                } else {
                    throw new Error('Gagal memuat data');
                }
            } catch (error) {
                console.error('Error loading transactions:', error);
                document.getElementById('transactionsTableBody').innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm font-semibold text-red-500">
                            Gagal memuat data transaksi. Silakan refresh halaman.
                        </td>
                    </tr>
                `;
            }
        }

        // Merender transaksi ke tabel sesuai filter yang sedang aktif.
        function renderTransactions() {
            const tbody = document.getElementById('transactionsTableBody');
            const filterVal = document.getElementById('timeFilter').value;

            let filteredData = allTransactions;

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (filterVal === 'today') {
                // Filter transaksi yang dibuat pada tanggal hari ini.
                filteredData = allTransactions.filter(trx => {
                    const trxDate = new Date(trx.created_at);
                    trxDate.setHours(0, 0, 0, 0);

                    return trxDate.getTime() === today.getTime();
                });
            } else if (filterVal === 'custom') {
                // Filter transaksi berdasarkan rentang tanggal yang dipilih user.
                const startVal = document.getElementById('startDate').value;
                const endVal = document.getElementById('endDate').value;

                if (startVal && endVal) {
                    const startDate = new Date(startVal);
                    startDate.setHours(0, 0, 0, 0);

                    const endDate = new Date(endVal);
                    endDate.setHours(23, 59, 59, 999);

                    filteredData = allTransactions.filter(trx => {
                        const trxDate = new Date(trx.created_at);

                        return trxDate >= startDate && trxDate <= endDate;
                    });
                }
            }

            // Mengubah angka nominal menjadi format Rupiah.
            const formatRupiah = (num) => {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(num);
            };

            if (filteredData.length > 0) {
                // Render baris transaksi jika data tersedia.
                tbody.innerHTML = filteredData.map((trx, index) => {
                    const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-slate-50';
                    const methodBadge = paymentMethodBadge(trx);
                    const statusHtml = transactionStatusBadge(trx.status);
                    const amountClass = trx.status === 'cancelled'
                        ? 'text-slate-400 line-through'
                        : 'text-slate-900';

                    const dateObj = new Date(trx.created_at);
                    const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                    const timeStr = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                    return `
                        <tr class="${rowClass} hover:bg-slate-50">
                            <td class="border-b border-slate-100 px-6 py-5 align-middle font-['Plus_Jakarta_Sans'] text-sm font-bold text-black">
                                ${trx.customer_name || '-'}
                            </td>
                            <td class="border-b border-slate-100 px-6 py-5 align-middle">
                                <!-- Tanggal dan jam transaksi -->
                                <div class="text-sm font-medium text-slate-700">${dateStr}</div>
                                <div class="text-xs text-slate-400">${timeStr} WIB</div>
                            </td>
                            <td class="border-b border-slate-100 px-6 py-5 align-middle text-sm font-bold ${amountClass}">
                                ${formatRupiah(trx.total_amount)}
                            </td>
                            <td class="border-b border-slate-100 px-6 py-5 align-middle">${methodBadge}</td>
                            <td class="border-b border-slate-100 px-6 py-5 align-middle">${statusHtml}</td>
                            <td class="border-b border-slate-100 px-6 py-5 align-middle">
                                <!-- Tombol untuk membuka preview struk -->
                                <button type="button" onclick="openStrukModal(${trx.transaction_id})"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition-colors hover:bg-blue-100">
                                    ${eyeIcon}
                                    Lihat
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                // Render empty state jika tidak ada transaksi yang sesuai filter.
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <!-- Ikon kosong dan pesan data tidak ditemukan -->
                            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
                                ${emptyIncomeIcon}
                            </div>
                            <h3 class="mb-2 text-sm font-bold text-slate-900">Tidak Ada Transaksi</h3>
                            <p class="text-sm text-slate-500">Tidak ada data transaksi yang sesuai dengan filter Anda.</p>
                        </td>
                    </tr>
                `;
            }
        }

        // Membuat badge metode pembayaran berdasarkan status dan jenis pembayaran.
        function paymentMethodBadge(transaction) {
            if (transaction.status !== 'completed') {
                return '<span class="text-xs font-semibold text-slate-400">-</span>';
            }

            return transaction.payment_method === 'qris'
                ? '<span class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold tracking-wide text-blue-600">QRIS</span>'
                : '<span class="inline-flex items-center rounded-full border border-green-100 bg-green-50 px-3 py-1.5 text-xs font-semibold tracking-wide text-green-600">Tunai</span>';
        }

        // Membuat tampilan status transaksi: berhasil, tertahan, atau dibatalkan.
        function transactionStatusBadge(status) {
            const statusMap = {
                completed: {
                    label: 'Berhasil',
                    wrapper: 'text-emerald-500',
                    dot: 'bg-emerald-500',
                },
                pending: {
                    label: 'Tertahan',
                    wrapper: 'text-amber-500',
                    dot: 'bg-amber-500',
                },
                cancelled: {
                    label: 'Dibatalkan',
                    wrapper: 'text-red-500',
                    dot: 'bg-red-500',
                },
            };

            const item = statusMap[status] || {
                label: '-',
                wrapper: 'text-slate-400',
                dot: 'bg-slate-400',
            };

            return `
                <div class="flex items-center text-xs font-semibold ${item.wrapper}">
                    <span class="mr-1.5 inline-block h-1.5 w-1.5 rounded-full ${item.dot}"></span>
                    ${item.label}
                </div>
            `;
        }

        // Membuka modal preview struk dan mengisi iframe dengan URL struk transaksi.
        function openStrukModal(id) {
            const modal = document.getElementById('strukModal');
            const modalBox = document.getElementById('strukModalBox');
            const transaction = allTransactions.find(item => Number(item.transaction_id) === Number(id));

            currentStrukTransactionId = id;
            document.getElementById('strukIframe').src = '/cashier/struk/' + id + '?preview=1';
            document.getElementById('incomeWaNumber').value = transaction?.phone_number || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                modalBox.classList.remove('scale-95');
                modalBox.classList.add('scale-100');
            });
        }

        // Menutup modal preview struk dan membersihkan isi iframe.
        function closeStrukModal() {
            const modal = document.getElementById('strukModal');
            const modalBox = document.getElementById('strukModalBox');

            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            modalBox.classList.remove('scale-100');
            modalBox.classList.add('scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.getElementById('strukIframe').src = '';
                document.getElementById('incomeWaNumber').value = '';
                currentStrukTransactionId = null;
            }, 200);
        }

        // Menormalkan nomor WhatsApp ke format Indonesia.
        function normalizePhone(value) {
            let phone = String(value ?? '').trim().replace(/[^\d+]/g, '');

            if (phone.startsWith('+')) phone = phone.substring(1);
            if (phone.startsWith('0')) phone = '62' + phone.substring(1);

            return phone;
        }

        // Mengirim link struk transaksi melalui WhatsApp.
        async function sendIncomeWhatsapp() {
            const input = document.getElementById('incomeWaNumber');
            const button = document.getElementById('btnIncomeWa');
            const phone = normalizePhone(input.value);

            if (!currentStrukTransactionId) {
                alert('Transaksi belum dipilih.');
                return;
            }

            if (!phone) {
                alert('Masukkan nomor WhatsApp pelanggan terlebih dahulu.');
                return;
            }

            button.disabled = true;
            button.innerText = 'Mengirim...';

            try {
                const response = await fetch('/cashier/send-whatsapp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        phone,
                        transaction_id: currentStrukTransactionId,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal mengirim WhatsApp.');
                }

                alert('Link struk WhatsApp berhasil dikirim ke pelanggan.');
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan jaringan/server.');
            } finally {
                button.disabled = false;
                button.innerHTML = `
                    ${whatsappIcon}
                    Kirim WhatsApp
                `;
            }
        }

        // Menutup modal struk saat user menekan area overlay di luar kotak modal.
        document.getElementById('strukModal').addEventListener('click', function (event) {
            if (event.target === this) closeStrukModal();
        });
    </script>
@endsection
