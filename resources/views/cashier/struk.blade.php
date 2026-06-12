@php
    $isPreview = request()->has('preview');
    $paperWidth = (int) request('paper_width', 80);

    if ($paperWidth < 45 || $paperWidth > 120) {
        $paperWidth = 80;
    }

    $paperPadding = $paperWidth <= 58 ? 3 : 4;
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 dark:bg-[#050505]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RealKasir - Struk Pembayaran</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --receipt-width: {{ $paperWidth }}mm;
            --receipt-padding: {{ $paperPadding }}mm;
            --receipt-font-size: {{ $paperWidth <= 58 ? '10.5px' : '11.5px' }};
        }

        .receipt-screen {
            width: min(100%, calc(var(--receipt-width) + 32px));
            margin: 0 auto;
        }

        .receipt-paper {
            width: var(--receipt-width);
            max-width: 100%;
            margin: 0 auto;
            padding: var(--receipt-padding);
            background: #ffffff;
            color: #000000;
            font-family: "Courier New", Courier, monospace;
            font-size: var(--receipt-font-size);
            line-height: 1.35;
            box-shadow: 0 18px 40px -24px rgba(15, 23, 42, 0.65);
        }

        .receipt-paper,
        .receipt-paper * {
            box-sizing: border-box;
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

        .receipt-row {
            display: flex;
            justify-content: space-between;
            gap: 3mm;
            margin: 1.2mm 0;
        }

        .receipt-row-label {
            flex: 0 0 auto;
        }

        .receipt-row-value {
            min-width: 0;
            text-align: right;
            word-break: break-word;
        }

        .receipt-divider {
            border: 0;
            border-top: 1px dashed #000000;
            margin: 3mm 0;
        }

        .receipt-section-title {
            display: flex;
            justify-content: space-between;
            gap: 3mm;
            margin-bottom: 2mm;
            font-weight: 700;
        }

        .receipt-item {
            margin-bottom: 2.4mm;
        }

        .receipt-item-main {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 3mm;
            align-items: start;
        }

        .receipt-item-name {
            min-width: 0;
            font-weight: 700;
            word-break: break-word;
        }

        .receipt-item-total {
            white-space: nowrap;
            text-align: right;
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

        @media screen {
            .receipt-paper {
                border-radius: 2px;
            }
        }

        @media print {
            @page {
                size: {{ $paperWidth }}mm auto;
                margin: 0;
            }

            html,
            body {
                width: var(--receipt-width);
                min-width: var(--receipt-width);
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
                overflow: visible !important;
            }

            .screen-only,
            .nav-wrapper {
                display: none !important;
            }

            .print-root {
                display: block !important;
                width: var(--receipt-width) !important;
                min-height: auto !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                overflow: visible !important;
            }

            .receipt-screen {
                display: block !important;
                width: var(--receipt-width) !important;
                max-width: var(--receipt-width) !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }

            .receipt-stage {
                display: block !important;
                width: var(--receipt-width) !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                overflow: visible !important;
            }

            .receipt-paper {
                width: var(--receipt-width) !important;
                max-width: var(--receipt-width) !important;
                margin: 0 !important;
                padding: var(--receipt-padding) !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body
    class="h-full w-full flex flex-col {{ $isPreview ? 'bg-white overflow-auto' : 'bg-slate-50 dark:bg-[#050505] overflow-hidden' }} font-sans antialiased text-slate-800 dark:text-slate-100">

    @if(!$isPreview)
        {{-- Navigasi atas halaman struk saat dibuka dari kasir --}}
        <div class="screen-only w-full shrink-0 z-50 nav-wrapper">
            @include('layouts.navigation')
        </div>
    @endif

    <main class="print-root {{ $isPreview ? 'flex justify-center items-start p-6' : 'flex flex-1 min-h-0 h-[calc(100vh-70px)] flex-col lg:flex-row' }}">
        <section class="receipt-stage flex-1 flex items-start justify-center bg-slate-200 p-6 overflow-y-auto">
            {{-- Tampilan kertas struk 80mm --}}
            <div class="receipt-screen">
                <article class="receipt-paper" aria-label="Struk pembayaran">
                    <header class="receipt-header">
                        {{-- Identitas toko pada struk --}}
                        <div class="receipt-brand">
                            <span class="receipt-brand-main">Warung Bakso</span>
                            <span class="receipt-brand-accent">Panjang Rezeki</span>
                        </div>
                        {{-- Judul dokumen struk --}}
                        <div class="receipt-title">Struk Pembayaran</div>
                    </header>

                    <hr class="receipt-divider">

                    {{-- Informasi dasar transaksi --}}
                    <div class="receipt-row">
                        <span class="receipt-row-label">No. Transaksi</span>
                        <span class="receipt-row-value">#{{ $transaction->transaction_id }}</span>
                    </div>
                    {{-- Nama kasir transaksi --}}
                    <div class="receipt-row">
                        <span class="receipt-row-label">Kasir</span>
                        <span class="receipt-row-value">{{ $transaction->user->name ?? 'Kasir' }}</span>
                    </div>
                    {{-- Waktu transaksi --}}
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

                    {{-- Header daftar pesanan --}}
                    <div class="receipt-section-title">
                        <span>Pesanan</span>
                        <span>Total</span>
                    </div>

                    @foreach($transaction->items as $item)
                        {{-- Item pesanan pada struk --}}
                        <div class="receipt-item">
                            <div class="receipt-item-main">
                                <div>
                                    <div class="receipt-item-name">{{ $item->product->name ?? 'Produk tidak ditemukan' }}</div>
                                    <div class="receipt-item-meta">
                                        {{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}
                                    </div>
                                </div>
                                {{-- Subtotal item pesanan --}}
                                <div class="receipt-item-total">
                                    {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                </div>
                            </div>

                            @if($item->note)
                                {{-- Catatan item pesanan --}}
                                <div class="receipt-note">
                                    @foreach(explode(' | ', $item->note) as $note)
                                        <div>- {{ $note }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <hr class="receipt-divider">

                    {{-- Ringkasan pembayaran --}}
                    <div class="receipt-row receipt-summary">
                        <span class="receipt-row-label">Metode</span>
                        <span class="receipt-row-value">{{ $transaction->payment_method === 'cash' ? 'Tunai' : 'QRIS' }}</span>
                    </div>
                    {{-- Total akhir transaksi --}}
                    <div class="receipt-row receipt-total">
                        <span class="receipt-row-label">Total</span>
                        <span class="receipt-row-value">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($transaction->payment_method === 'cash')
                        {{-- Detail pembayaran tunai --}}
                        <div class="receipt-row">
                            <span class="receipt-row-label">Tunai</span>
                            <span class="receipt-row-value">Rp {{ number_format($transaction->cash_amount, 0, ',', '.') }}</span>
                        </div>
                        {{-- Nominal kembalian transaksi tunai --}}
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
            </div>
        </section>

        @if(!$isPreview)
            <aside
                class="screen-only flex-1 bg-white dark:bg-[#0a0a0a] flex flex-col justify-center gap-6 p-6 lg:p-12 border-t lg:border-t-0 lg:border-l border-slate-200 dark:border-slate-800 overflow-y-auto">

                {{-- Pesan sukses transaksi --}}
                <div class="text-center mb-2">
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">Transaksi Berhasil!</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">Silakan cetak struk atau kirim ke pelanggan.</p>
                </div>

                {{-- Panel aksi cetak struk --}}
                <div class="bg-slate-50 dark:bg-[#111] border border-slate-200 dark:border-slate-800 rounded-2xl p-8">
                    <div class="flex items-center gap-3 text-xl font-bold text-slate-900 dark:text-white mb-6">
                        <x-icons.printer class="h-6 w-6" />
                        Cetak Struk
                    </div>
                    <button onclick="window.print()"
                        class="w-full py-4 text-base bg-slate-900 hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200 text-white font-semibold rounded-xl flex items-center justify-center gap-3 transition-colors cursor-pointer">
                        Cetak Sekarang
                    </button>
                </div>

                {{-- Panel kirim struk via WhatsApp --}}
                <div class="bg-slate-50 dark:bg-[#111] border border-slate-200 dark:border-slate-800 rounded-2xl p-8">
                    <div class="flex items-center gap-3 text-xl font-bold text-slate-900 dark:text-white mb-6">
                        <x-icons.chat-bubble-left-right class="h-6 w-6" />
                        Kirim WhatsApp
                    </div>
                    <input type="text" id="waNumber"
                        class="w-full py-3 px-4 border border-slate-300 dark:border-slate-700 dark:bg-[#1a1a1a] dark:text-white rounded-xl mb-4 text-base focus:outline-none focus:ring-2 focus:ring-green-400"
                        placeholder="Nomor WhatsApp (08xxx)" value="{{ $transaction->phone_number }}">
                    <button onclick="sendWhatsapp()" id="btnWa"
                        class="w-full py-4 bg-[#25D366] hover:bg-[#128C7E] text-white font-semibold rounded-xl flex items-center justify-center gap-3 transition-colors cursor-pointer border-0">
                        Kirim via WhatsApp
                    </button>
                </div>

                <button onclick="window.location.href='/cashier'"
                    class="w-full py-4 bg-white dark:bg-transparent border border-slate-300 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 hover:text-slate-900 dark:hover:text-white font-semibold rounded-xl transition-colors cursor-pointer">
                    Kembali ke Menu Kasir
                </button>
            </aside>
        @endif
    </main>

    <script>
        const shouldAutoPrint = @json(request()->boolean('print') && !$isPreview);

        // Menormalkan nomor WhatsApp ke format Indonesia.
        function normalizePhone(value) {
            let phone = String(value ?? '').trim().replace(/[^\d+]/g, '');
            if (phone.startsWith('+')) phone = phone.substring(1);
            if (phone.startsWith('0')) phone = '62' + phone.substring(1);
            return phone;
        }

        window.addEventListener('load', () => {
            if (shouldAutoPrint) {
                setTimeout(() => window.print(), 300);
            }
        });

        // Mengirim link struk ke pelanggan melalui WhatsApp.
        async function sendWhatsapp() {
            let phone = normalizePhone(document.getElementById('waNumber').value);
            const btn = document.getElementById('btnWa');

            if (!phone) return alert('Masukkan nomor WhatsApp pelanggan terlebih dahulu.');

            btn.disabled = true;
            btn.innerHTML = 'Mengirim...';

            try {
                const res = await fetch('/cashier/send-whatsapp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        phone: phone,
                        transaction_id: {{ $transaction->transaction_id }}
                    })
                });

                const data = await res.json();

                if (res.ok) {
                    alert('Link Struk WhatsApp berhasil dikirim ke pelanggan.');
                    document.getElementById('waNumber').value = '';
                } else {
                    alert(data.message || 'Gagal mengirim pesan.');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan jaringan/server.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Kirim via WhatsApp';
            }
        }
    </script>
</body>

</html>
