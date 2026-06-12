@php
    $paymentLabel = fn ($method) => strtolower((string) $method) === 'cash' ? 'Tunai' : strtoupper((string) $method);
    $expenseCategoryLabels = [
        'ingredient' => 'Bahan',
        'operational' => 'Operasional',
        'others' => 'Lain-lain',
    ];
    $expenseCategoryLabel = fn ($category) => $expenseCategoryLabels[strtolower((string) $category)] ?? ucfirst((string) $category);
@endphp

{{-- Header identitas usaha dan periode laporan --}}
<div class="text-center mb-6 pb-4 border-b-2 border-slate-800">
    <h5 class="font-bold mb-1 text-slate-900 text-lg uppercase tracking-wider">Warung Bakso Panjang Rezeki</h5>
    <p class="text-slate-700 text-sm">
        Laporan Keuangan Periode:
        <strong>{{ $startDate->format('d/m/Y') }}</strong>
        -
        <strong>{{ $endDate->format('d/m/Y') }}</strong>
    </p>
</div>

{{-- Wrapper utama isi pratinjau laporan --}}
<div class="flex flex-col gap-6">
    {{-- Card bagian pemasukan transaksi --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        {{-- Header bagian pemasukan --}}
        <div class="bg-emerald-50 px-5 py-3 border-b border-emerald-100">
            <h6 class="font-bold text-emerald-700 m-0">A. Pemasukan (Transaksi)</h6>
        </div>

        @if($incomes->count() > 0)
            {{-- Wrapper scroll tabel daftar pemasukan --}}
            <div class="overflow-x-auto max-h-[350px]">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-3 text-center w-[5%]">No</th>
                            <th class="px-4 py-3 w-[22%]">Nama Pelanggan</th>
                            <th class="px-4 py-3 w-[18%]">Nomor Telepon</th>
                            <th class="px-4 py-3 w-[20%]">Tanggal</th>
                            <th class="px-4 py-3 w-[15%]">Metode</th>
                            <th class="px-6 py-3 text-right w-[20%]">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($incomes as $index => $income)
                            <tr class="bg-white hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-center font-medium">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">{{ $income->customer_name ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $income->phone_number ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $income->created_at->translatedFormat('l, d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $paymentLabel($income->payment_method) }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-slate-700">Rp {{ number_format($income->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Bar total pemasukan --}}
            <div class="bg-slate-50 p-4 text-right border-t border-slate-200">
                <span class="text-slate-500 mr-4 font-medium">Total Pemasukan</span>
                <span class="font-bold text-emerald-600 text-lg tracking-tight">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
            </div>
        @else
            {{-- Empty state pemasukan --}}
            <div class="p-8 text-center text-slate-400">Tidak ada data pemasukan.</div>
        @endif

        {{-- Area analisis pemasukan tambahan --}}
        <div class="border-t border-slate-200 p-5">
            <h6 class="font-bold text-slate-800 mb-3">Produk dengan Pemasukan Terbesar</h6>
            @if($topProducts->count() > 0)
                {{-- Wrapper scroll tabel produk dengan pemasukan terbesar --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3 text-center">Total Dibeli</th>
                                <th class="px-4 py-3">Terakhir Dibeli</th>
                                <th class="px-4 py-3 text-right">Total Pemasukan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($topProducts as $index => $product)
                                <tr>
                                    <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $product['product_name'] }}</td>
                                    <td class="px-4 py-3 text-center">{{ $product['quantity'] }}</td>
                                    <td class="px-4 py-3">{{ $product['last_transaction_at']->translatedFormat('l, d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($product['subtotal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-slate-400 m-0">Belum ada detail produk pada pemasukan periode ini.</p>
            @endif

            <h6 class="font-bold text-slate-800 mt-5 mb-3">Metode Pembayaran Paling Sering Digunakan</h6>
            @if($paymentMethods->count() > 0)
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                        <tr>
                            <th class="px-4 py-3">Metode</th>
                            <th class="px-4 py-3 text-center">Jumlah Transaksi</th>
                            <th class="px-4 py-3 text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($paymentMethods as $method)
                            <tr>
                                <td class="px-4 py-3 font-semibold">{{ $paymentLabel($method['method']) }}</td>
                                <td class="px-4 py-3 text-center">{{ $method['count'] }}</td>
                                <td class="px-4 py-3 text-right">Rp {{ number_format($method['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-slate-400 m-0">Belum ada metode pembayaran pada periode ini.</p>
            @endif
        </div>
    </div>

    {{-- Card bagian pengeluaran --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        {{-- Header bagian pengeluaran --}}
        <div class="bg-rose-50 px-5 py-3 border-b border-rose-100">
            <h6 class="font-bold text-rose-700 m-0">B. Pengeluaran</h6>
        </div>

        @if($expenses->count() > 0)
            {{-- Wrapper scroll tabel daftar pengeluaran --}}
            <div class="overflow-x-auto max-h-[350px]">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-3 text-center w-[5%]">No</th>
                            <th class="px-4 py-3 w-[22%]">Tanggal</th>
                            <th class="px-4 py-3 w-[20%]">Kategori</th>
                            <th class="px-4 py-3 w-[33%]">Keterangan</th>
                            <th class="px-6 py-3 text-right w-[20%]">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($expenses as $index => $expense)
                            <tr class="bg-white hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-center font-medium">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">{{ $expense->created_at->translatedFormat('l, d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $expenseCategoryLabel($expense->category) }}</td>
                                <td class="px-4 py-3">{{ $expense->description }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-rose-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Bar total pengeluaran --}}
            <div class="bg-slate-50 p-4 text-right border-t border-slate-200">
                <span class="text-slate-500 mr-4 font-medium">Total Pengeluaran</span>
                <span class="font-bold text-rose-600 text-lg tracking-tight">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
            </div>
        @else
            {{-- Empty state pengeluaran --}}
            <div class="p-8 text-center text-slate-400">Tidak ada data pengeluaran.</div>
        @endif

        {{-- Area analisis pengeluaran terbesar --}}
        <div class="border-t border-slate-200 p-5">
            <h6 class="font-bold text-slate-800 mb-3">Pengeluaran Terbesar</h6>
            @if($topExpenses->count() > 0)
                {{-- Wrapper scroll tabel pengeluaran terbesar --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Kategori</th>
                                <th class="px-4 py-3">Keterangan</th>
                                <th class="px-4 py-3 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($topExpenses as $index => $expense)
                                <tr>
                                    <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">{{ $expense->created_at->translatedFormat('l, d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">{{ $expenseCategoryLabel($expense->category) }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $expense->description }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-slate-400 m-0">Belum ada pengeluaran pada periode ini.</p>
            @endif
        </div>
    </div>

    {{-- ============================================================
        Ringkasan Laba Bersih
    ============================================================ --}}

    {{-- Card ringkasan laba bersih --}}
    <div class="bg-slate-50 border border-blue-200 rounded-xl p-5">
        <h6 class="font-bold text-slate-800 text-lg mb-4">Ringkasan Laba Bersih</h6>
        {{-- Grid nilai ringkasan pemasukan, pengeluaran, dan laba --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Ringkasan total pemasukan --}}
            <div>
                <span class="block text-xs font-bold uppercase tracking-wide text-slate-500">Total Pemasukan</span>
                <span class="font-bold text-emerald-600">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
            </div>
            {{-- Ringkasan total pengeluaran --}}
            <div>
                <span class="block text-xs font-bold uppercase tracking-wide text-slate-500">Total Pengeluaran</span>
                <span class="font-bold text-rose-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
            </div>
            {{-- Ringkasan penghasilan periode ini --}}
            <div>
                <span class="block text-xs font-bold uppercase tracking-wide text-slate-500">Penghasilan Periode Ini</span>
                <span class="font-bold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
