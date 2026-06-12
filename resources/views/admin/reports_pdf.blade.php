@php
    $paymentLabel = fn ($method) => strtolower((string) $method) === 'cash' ? 'Tunai' : strtoupper((string) $method);
    $expenseCategoryLabels = [
        'ingredient' => 'Bahan',
        'operational' => 'Operasional',
        'others' => 'Lain-lain',
    ];
    $expenseCategoryLabel = fn ($category) => $expenseCategoryLabels[strtolower((string) $category)] ?? ucfirst((string) $category);
@endphp

<!DOCTYPE html>
<html>

<head>
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 12px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 18px;
            margin-bottom: 8px;
            background: #f2f2f2;
            padding: 6px;
            border: 1px solid #ddd;
        }

        .sub-title {
            font-size: 12px;
            font-weight: bold;
            margin: 14px 0 7px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row td {
            font-weight: bold;
            background: #f8f9fa;
        }

        .summary-box {
            margin-top: 22px;
            border: 1px solid #222;
            padding: 10px;
            width: 45%;
            float: right;
        }
    </style>
</head>

<body>
    {{-- Header laporan PDF --}}
    <div class="header">
        <h1>Warung Bakso Panjang Rezeki</h1>
        <p>Laporan Keuangan Periode: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
    </div>

    {{-- Judul bagian pemasukan transaksi --}}
    <div class="section-title">A. Pemasukan (Transaksi)</div>
    @if($incomes->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="25">No</th>
                    <th>Nama Pelanggan</th>
                    <th>Nomor Telepon</th>
                    <th>Tanggal</th>
                    <th>Metode</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomes as $index => $income)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $income->customer_name ?: '-' }}</td>
                        <td>{{ $income->phone_number ?: '-' }}</td>
                        <td>{{ $income->created_at->translatedFormat('l, d/m/Y H:i') }}</td>
                        <td>{{ $paymentLabel($income->payment_method) }}</td>
                        <td class="text-right">Rp {{ number_format($income->total_amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" class="text-right">Total Pemasukan</td>
                    <td class="text-right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p>Tidak ada data pemasukan pada periode ini.</p>
    @endif

    {{-- Judul bagian produk dengan pemasukan terbesar --}}
    <div class="sub-title">Produk dengan Pemasukan Terbesar</div>
    @if($topProducts->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="25">No</th>
                    <th>Produk</th>
                    <th class="text-center">Total Dibeli</th>
                    <th>Terakhir Dibeli</th>
                    <th class="text-right">Total Pemasukan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $index => $product)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $product['product_name'] }}</td>
                        <td class="text-center">{{ $product['quantity'] }}</td>
                        <td>{{ $product['last_transaction_at']->translatedFormat('l, d/m/Y H:i') }}</td>
                        <td class="text-right">Rp {{ number_format($product['subtotal'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Belum ada detail produk pada pemasukan periode ini.</p>
    @endif

    {{-- Judul bagian metode pembayaran teratas --}}
    <div class="sub-title">Metode Pembayaran Paling Sering Digunakan</div>
    @if($paymentMethods->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Metode</th>
                    <th class="text-center">Jumlah Transaksi</th>
                    <th class="text-right">Total Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paymentMethods as $method)
                    <tr>
                        <td>{{ $paymentLabel($method['method']) }}</td>
                        <td class="text-center">{{ $method['count'] }}</td>
                        <td class="text-right">Rp {{ number_format($method['total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Belum ada metode pembayaran pada periode ini.</p>
    @endif

    {{-- Judul bagian pengeluaran --}}
    <div class="section-title">B. Pengeluaran</div>
    @if($expenses->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="25">No</th>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $index => $expense)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $expense->created_at->translatedFormat('l, d/m/Y H:i') }}</td>
                        <td>{{ $expenseCategoryLabel($expense->category) }}</td>
                        <td>{{ $expense->description }}</td>
                        <td class="text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="text-right">Total Pengeluaran</td>
                    <td class="text-right">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p>Tidak ada data pengeluaran pada periode ini.</p>
    @endif

    {{-- Judul bagian pengeluaran terbesar --}}
    <div class="sub-title">Pengeluaran Terbesar</div>
    @if($topExpenses->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="25">No</th>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topExpenses as $index => $expense)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $expense->created_at->translatedFormat('l, d/m/Y H:i') }}</td>
                        <td>{{ $expenseCategoryLabel($expense->category) }}</td>
                        <td>{{ $expense->description }}</td>
                        <td class="text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Belum ada pengeluaran pada periode ini.</p>
    @endif

    {{-- ============================================================
        Ringkasan Laba Bersih
    ============================================================ --}}

    {{-- Kotak ringkasan laba bersih --}}
    <div class="summary-box">
        <table style="border: none; margin: 0;">
            <tr>
                <td style="border: none;">Total Pemasukan</td>
                <td style="border: none;" class="text-right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: none;">Total Pengeluaran</td>
                <td style="border: none;" class="text-right">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: none; border-top: 1px solid #222; padding-top: 8px;"><strong>LABA BERSIH</strong></td>
                <td style="border: none; border-top: 1px solid #222; padding-top: 8px;" class="text-right">
                    <strong>Rp {{ number_format($netProfit, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
