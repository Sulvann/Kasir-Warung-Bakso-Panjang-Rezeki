@extends('layouts.admin')

@section('content')
    {{-- Header halaman dashboard admin --}}
    <div class="page-header">
        <h1 class="page-title text-2xl font-bold tracking-tight text-[#001f5b]">Dashboard Overview</h1>
    </div>

    {{-- Grid kartu ringkasan dashboard --}}
    <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        {{-- Kartu ringkasan pemasukan --}}
        <div class="flex flex-col justify-center rounded-2xl border-[3px] border-emerald-500 bg-white p-6 shadow-md shadow-black/5">
            {{-- Header kartu pemasukan --}}
            <div class="mb-2 flex items-start justify-between gap-4">
                <h3 class="m-0 text-xs font-bold uppercase tracking-wider text-slate-500">Total Pemasukan</h3>
                <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-[10px] bg-emerald-50 text-emerald-500">
                    <x-icons.trending-up class="h-[22px] w-[22px]" />
                </span>
            </div>
            {{-- Nilai total pemasukan --}}
            <div class="font-['Plus_Jakarta_Sans'] text-3xl font-extrabold tracking-tight text-emerald-500">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
            <small class="mt-1 text-xs font-medium text-slate-500">Total omzet kotor</small>
        </div>

        {{-- Kartu ringkasan pengeluaran --}}
        <div class="flex flex-col justify-center rounded-2xl border-[3px] border-red-500 bg-white p-6 shadow-md shadow-black/5">
            {{-- Header kartu pengeluaran --}}
            <div class="mb-2 flex items-start justify-between gap-4">
                <h3 class="m-0 text-xs font-bold uppercase tracking-wider text-slate-500">Total Pengeluaran</h3>
                <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-[10px] bg-red-50 text-red-500">
                    <x-icons.trending-down class="h-[22px] w-[22px]" />
                </span>
            </div>
            {{-- Nilai total pengeluaran --}}
            <div class="font-['Plus_Jakarta_Sans'] text-3xl font-extrabold tracking-tight text-red-500">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
            <small class="mt-1 text-xs font-medium text-slate-500">Biaya operasional</small>
        </div>

        {{-- Kartu ringkasan laba bersih --}}
        <div class="flex flex-col justify-center rounded-2xl border-[3px] border-blue-500 bg-white p-6 shadow-md shadow-black/5">
            {{-- Header kartu laba bersih --}}
            <div class="mb-2 flex items-start justify-between gap-4">
                <h3 class="m-0 text-xs font-bold uppercase tracking-wider text-slate-500">Laba Bersih</h3>
                <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-[10px] bg-blue-50 text-blue-500">
                    <x-icons.document-chart-bar class="h-[22px] w-[22px]" />
                </span>
            </div>
            {{-- Nilai laba bersih --}}
            <div class="font-['Plus_Jakarta_Sans'] text-3xl font-extrabold tracking-tight text-blue-500">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
            <small class="mt-1 text-xs font-medium text-slate-500">(Pemasukan - Pengeluaran)</small>
        </div>
    </div>

    {{-- Judul bagian grafik penjualan --}}
    <div class="mb-4 text-lg font-bold tracking-tight text-[#001f5b]">Tren Penjualan (30 Hari Terakhir)</div>

    {{-- Card grafik tren penjualan --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-md shadow-black/5">
        {{-- Header informasi update grafik --}}
        <div class="mb-6 flex items-center justify-between">
            <div></div>
            <div class="text-xs font-medium text-slate-500">
                Update Terakhir: {{ now()->format('H:i') }}
            </div>
        </div>
        {{-- Area canvas grafik penjualan --}}
        <div class="relative h-[400px] w-full">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Menyiapkan grafik tren penjualan setelah halaman selesai dimuat.
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart').getContext('2d');

            // Data grafik dari controller.
            const labels = @json($chartDates);
            const data = @json($chartIncome);

            // Warna gradasi area bawah garis grafik.
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendapatan (IDR)',
                        data: data,
                        borderWidth: 3,
                        borderColor: '#10b981', // Green
                        backgroundColor: gradient,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4 // Curvy lines
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: {
                                size: 13,
                                family: "'Plus Jakarta Sans', sans-serif"
                            },
                            bodyFont: {
                                size: 14,
                                weight: 'bold',
                                family: "'Plus Jakarta Sans', sans-serif"
                            },
                            callbacks: {
                                // Format angka tooltip menjadi Rupiah.
                                label: function (context) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                fontFamily: "'Plus Jakarta Sans', sans-serif",
                                color: '#64748b'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            border: {
                                dash: [4, 4]
                            },
                            grid: {
                                color: '#f1f5f9',
                                borderDash: [4, 4]
                            },
                            ticks: {
                                fontFamily: "'Plus Jakarta Sans', sans-serif",
                                color: '#64748b',
                                // Menyederhanakan label sumbu Y agar mudah dibaca.
                                callback: function (value) {
                                    if (value >= 1000000) return (value / 1000000) + 'jt';
                                    if (value >= 1000) return (value / 1000) + 'rb';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
