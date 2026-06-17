@php
    $money = fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.');
    $dateTime = fn ($date) => $date->translatedFormat('l, d/m/Y H:i');
    $paymentLabel = fn ($method) => strtolower((string) $method) === 'cash' ? 'Tunai' : strtoupper((string) $method);
    $expenseCategoryLabels = [
        'ingredient' => 'Bahan',
        'operational' => 'Operasional',
        'others' => 'Lain-lain',
    ];
    $expenseCategoryLabel = fn ($category) => $expenseCategoryLabels[strtolower((string) $category)] ?? ucfirst((string) $category);
@endphp

{{-- Workbook preview laporan yang menyerupai tampilan sheet Excel --}}
<div data-report-preview-tabs class="bg-slate-100">
    {{-- Header workbook --}}
    <div class="border-b border-slate-300 bg-white p-5">
        <div class="flex flex-col gap-1">
            <h4 class="text-base font-black uppercase tracking-wide text-slate-900">Warung Bakso Panjang Rezeki</h4>
            <p class="text-sm font-medium text-slate-600">
                Laporan Keuangan Periode {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
            </p>
        </div>
    </div>

    {{-- Tab sheet laporan --}}
    <div class="flex gap-1 overflow-x-auto border-b border-slate-300 bg-slate-200 px-3 pt-3">
        <button type="button" data-report-tab="summary"
            class="whitespace-nowrap rounded-t-lg border border-b-0 px-4 py-2 text-xs font-bold transition">
            Ringkasan
        </button>
        <button type="button" data-report-tab="incomes"
            class="whitespace-nowrap rounded-t-lg border border-b-0 px-4 py-2 text-xs font-bold transition">
            Pemasukan
        </button>
        <button type="button" data-report-tab="expenses"
            class="whitespace-nowrap rounded-t-lg border border-b-0 px-4 py-2 text-xs font-bold transition">
            Pengeluaran
        </button>
        <button type="button" data-report-tab="top-products"
            class="whitespace-nowrap rounded-t-lg border border-b-0 px-4 py-2 text-xs font-bold transition">
            Produk Terbesar
        </button>
        <button type="button" data-report-tab="payment-methods"
            class="whitespace-nowrap rounded-t-lg border border-b-0 px-4 py-2 text-xs font-bold transition">
            Metode Bayar
        </button>
        <button type="button" data-report-tab="top-expenses"
            class="whitespace-nowrap rounded-t-lg border border-b-0 px-4 py-2 text-xs font-bold transition">
            Pengeluaran Terbesar
        </button>
        <button type="button" data-report-tab="sales-trend"
            class="whitespace-nowrap rounded-t-lg border border-b-0 px-4 py-2 text-xs font-bold transition">
            Tren Penjualan
        </button>
    </div>

    {{-- Area isi sheet --}}
    <div class="bg-white p-4">
        <section data-report-tab-panel="summary">
            <div class="overflow-x-auto">
                <table class="min-w-[560px] border-collapse text-xs text-slate-800">
                    <tbody>
                        <tr>
                            <td colspan="2" class="border border-slate-300 bg-[#d9eaf7] px-3 py-2 font-black uppercase">
                                Warung Bakso Panjang Rezeki
                            </td>
                        </tr>
                        <tr>
                            <td class="w-[220px] border border-slate-300 px-3 py-2 font-bold">Laporan Keuangan Periode</td>
                            <td class="border border-slate-300 px-3 py-2">{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-300 bg-slate-50 px-3 py-2 font-bold">Keterangan</td>
                            <td class="border border-slate-300 bg-slate-50 px-3 py-2 text-right font-bold">Nominal</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-300 px-3 py-2">Total Pemasukan</td>
                            <td class="border border-slate-300 px-3 py-2 text-right font-semibold text-emerald-700">{{ $money($totalIncome) }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-300 px-3 py-2">Total Pengeluaran</td>
                            <td class="border border-slate-300 px-3 py-2 text-right font-semibold text-rose-700">{{ $money($totalExpense) }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-300 bg-slate-50 px-3 py-2 font-black">Laba Bersih</td>
                            <td class="border border-slate-300 bg-slate-50 px-3 py-2 text-right font-black {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $money($netProfit) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section data-report-tab-panel="incomes" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-[900px] border-collapse text-xs text-slate-800">
                    <thead>
                        <tr class="bg-[#dff5e8]">
                            <th class="border border-slate-300 px-3 py-2 text-center">No</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Nama Pelanggan</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Nomor Telepon</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Tanggal</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Metode</th>
                            <th class="border border-slate-300 px-3 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomes as $index => $income)
                            <tr>
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $income->customer_name ?: '-' }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $income->phone_number ?: '-' }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $dateTime($income->created_at) }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $paymentLabel($income->payment_method) }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ $money($income->total_amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border border-slate-300 px-3 py-6 text-center text-slate-500">
                                    Tidak ada data pemasukan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                        <tr class="bg-slate-50 font-black">
                            <td colspan="5" class="border border-slate-300 px-3 py-2 text-right">Total Pemasukan</td>
                            <td class="border border-slate-300 px-3 py-2 text-right text-emerald-700">{{ $money($totalIncome) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section data-report-tab-panel="expenses" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-[820px] border-collapse text-xs text-slate-800">
                    <thead>
                        <tr class="bg-[#fde2e2]">
                            <th class="border border-slate-300 px-3 py-2 text-center">No</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Tanggal</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Kategori</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Keterangan</th>
                            <th class="border border-slate-300 px-3 py-2 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $index => $expense)
                            <tr>
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $dateTime($expense->created_at) }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $expenseCategoryLabel($expense->category) }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $expense->description }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ $money($expense->amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="border border-slate-300 px-3 py-6 text-center text-slate-500">
                                    Tidak ada data pengeluaran pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                        <tr class="bg-slate-50 font-black">
                            <td colspan="4" class="border border-slate-300 px-3 py-2 text-right">Total Pengeluaran</td>
                            <td class="border border-slate-300 px-3 py-2 text-right text-rose-700">{{ $money($totalExpense) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section data-report-tab-panel="top-products" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-[780px] border-collapse text-xs text-slate-800">
                    <thead>
                        <tr class="bg-[#d9eaf7]">
                            <th class="border border-slate-300 px-3 py-2 text-center">No</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Produk</th>
                            <th class="border border-slate-300 px-3 py-2 text-center">Total Dibeli</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Terakhir Dibeli</th>
                            <th class="border border-slate-300 px-3 py-2 text-right">Total Pemasukan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $index => $product)
                            <tr>
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="border border-slate-300 px-3 py-2 font-semibold">{{ $product['product_name'] }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $product['quantity'] }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $dateTime($product['last_transaction_at']) }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ $money($product['subtotal']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="border border-slate-300 px-3 py-6 text-center text-slate-500">
                                    Belum ada detail produk pada pemasukan periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section data-report-tab-panel="payment-methods" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-[620px] border-collapse text-xs text-slate-800">
                    <thead>
                        <tr class="bg-[#d9eaf7]">
                            <th class="border border-slate-300 px-3 py-2 text-center">No</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Metode</th>
                            <th class="border border-slate-300 px-3 py-2 text-center">Jumlah Transaksi</th>
                            <th class="border border-slate-300 px-3 py-2 text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentMethods as $index => $method)
                            <tr>
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="border border-slate-300 px-3 py-2 font-semibold">{{ $paymentLabel($method['method']) }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $method['count'] }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ $money($method['total']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="border border-slate-300 px-3 py-6 text-center text-slate-500">
                                    Belum ada metode pembayaran pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section data-report-tab-panel="top-expenses" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-[760px] border-collapse text-xs text-slate-800">
                    <thead>
                        <tr class="bg-[#fde2e2]">
                            <th class="border border-slate-300 px-3 py-2 text-center">No</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Tanggal</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Kategori</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Keterangan</th>
                            <th class="border border-slate-300 px-3 py-2 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topExpenses as $index => $expense)
                            <tr>
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $dateTime($expense->created_at) }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $expenseCategoryLabel($expense->category) }}</td>
                                <td class="border border-slate-300 px-3 py-2 font-semibold">{{ $expense->description }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ $money($expense->amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="border border-slate-300 px-3 py-6 text-center text-slate-500">
                                    Belum ada pengeluaran pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section data-report-tab-panel="sales-trend" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-[520px] border-collapse text-xs text-slate-800">
                    <thead>
                        <tr class="bg-[#d9eaf7]">
                            <th class="border border-slate-300 px-3 py-2 text-center">No</th>
                            <th class="border border-slate-300 px-3 py-2 text-left">Tanggal</th>
                            <th class="border border-slate-300 px-3 py-2 text-right">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesTrend as $index => $trend)
                            <tr>
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $trend['date'] }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ $money($trend['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
