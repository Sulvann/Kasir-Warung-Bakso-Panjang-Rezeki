@php
    $paperWidth = isset($paperWidth) ? (int) $paperWidth : 80;

    if ($paperWidth < 45 || $paperWidth > 120) {
        $paperWidth = 80;
    }

    $paperPadding = isset($paperPadding) ? (int) $paperPadding : ($paperWidth <= 58 ? 3 : 4);
    $fontSize = $paperWidth <= 58 ? '10.5px' : '11.5px';
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Struk Pembayaran #{{ $transaction->transaction_id }}</title>
    <style>
        @page {
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #000000;
        }

        body {
            font-family: "Courier New", Courier, monospace;
            font-size: {{ $fontSize }};
            line-height: 1.35;
        }

        .receipt-paper,
        .receipt-paper * {
            box-sizing: border-box;
        }

        .receipt-paper {
            width: auto;
            padding: {{ $paperPadding }}mm;
            background: #ffffff;
            color: #000000;
        }

        .receipt-header,
        .receipt-footer {
            text-align: center;
        }

        .receipt-brand {
            margin-bottom: 2mm;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .receipt-brand-main {
            display: block;
            font-weight: 700;
            font-size: 1.1em;
        }

        .receipt-brand-accent {
            display: block;
            color: #d60000;
            font-weight: 900;
            font-size: 1.18em;
        }

        .receipt-title {
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .receipt-divider {
            border: 0;
            border-top: 1px dashed #000000;
            margin: 3mm 0;
        }

        .receipt-row,
        .receipt-section-title,
        .receipt-item-main {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .receipt-row {
            margin: 1.2mm 0;
        }

        .receipt-row-label,
        .receipt-row-value,
        .receipt-section-left,
        .receipt-section-right,
        .receipt-item-left,
        .receipt-item-total {
            display: table-cell;
            vertical-align: top;
        }

        .receipt-row-label {
            width: 42%;
        }

        .receipt-row-value {
            width: 58%;
            text-align: right;
            word-wrap: break-word;
        }

        .receipt-section-title {
            margin-bottom: 2mm;
            font-weight: 700;
        }

        .receipt-section-right,
        .receipt-item-total {
            text-align: right;
            white-space: nowrap;
        }

        .receipt-item {
            margin-bottom: 2.4mm;
        }

        .receipt-item-left {
            width: 64%;
        }

        .receipt-item-total {
            width: 36%;
        }

        .receipt-item-name {
            font-weight: 700;
            word-wrap: break-word;
        }

        .receipt-item-meta,
        .receipt-note {
            color: #333333;
            font-size: 0.9em;
        }

        .receipt-note {
            margin-top: 1mm;
            padding-left: 2mm;
            font-style: italic;
        }

        .receipt-summary {
            font-weight: 700;
        }

        .receipt-total {
            font-size: 1.15em;
            font-weight: 900;
        }

        .receipt-footer {
            margin-top: 4mm;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <article class="receipt-paper" aria-label="Struk pembayaran">
        <header class="receipt-header">
            <div class="receipt-brand">
                <span class="receipt-brand-main">Warung Bakso</span>
                <span class="receipt-brand-accent">Panjang Rezeki</span>
            </div>
            <div class="receipt-title">Struk Pembayaran</div>
        </header>

        <hr class="receipt-divider">

        <div class="receipt-row">
            <span class="receipt-row-label">No. Transaksi</span>
            <span class="receipt-row-value">#{{ $transaction->transaction_id }}</span>
        </div>
        <div class="receipt-row">
            <span class="receipt-row-label">Kasir</span>
            <span class="receipt-row-value">{{ $transaction->user->name ?? 'Kasir' }}</span>
        </div>
        <div class="receipt-row">
            <span class="receipt-row-label">Waktu</span>
            <span class="receipt-row-value">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($transaction->customer_name)
            <div class="receipt-row">
                <span class="receipt-row-label">Pelanggan</span>
                <span class="receipt-row-value">{{ $transaction->customer_name }}</span>
            </div>
        @endif

        <hr class="receipt-divider">

        <div class="receipt-section-title">
            <span class="receipt-section-left">Pesanan</span>
            <span class="receipt-section-right">Total</span>
        </div>

        @foreach($transaction->items as $item)
            <div class="receipt-item">
                <div class="receipt-item-main">
                    <div class="receipt-item-left">
                        <div class="receipt-item-name">{{ $item->product->name ?? 'Produk tidak ditemukan' }}</div>
                        <div class="receipt-item-meta">
                            {{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="receipt-item-total">
                        {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                    </div>
                </div>

                @if($item->note)
                    <div class="receipt-note">
                        @foreach(explode(' | ', $item->note) as $note)
                            <div>- {{ $note }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        <hr class="receipt-divider">

        <div class="receipt-row receipt-summary">
            <span class="receipt-row-label">Metode</span>
            <span class="receipt-row-value">{{ $transaction->payment_method === 'cash' ? 'Tunai' : 'QRIS' }}</span>
        </div>
        <div class="receipt-row receipt-total">
            <span class="receipt-row-label">Total</span>
            <span class="receipt-row-value">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
        </div>

        @if($transaction->payment_method === 'cash')
            <div class="receipt-row">
                <span class="receipt-row-label">Tunai</span>
                <span class="receipt-row-value">Rp {{ number_format($transaction->cash_amount, 0, ',', '.') }}</span>
            </div>
            <div class="receipt-row">
                <span class="receipt-row-label">Kembali</span>
                <span class="receipt-row-value">Rp {{ number_format($transaction->change_amount ?? ($transaction->cash_amount - $transaction->total_amount), 0, ',', '.') }}</span>
            </div>
        @endif

        <hr class="receipt-divider">

        <footer class="receipt-footer">
            Terima Kasih Banyak
        </footer>
    </article>
</body>

</html>
