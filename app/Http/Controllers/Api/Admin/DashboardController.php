<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Menampilkan dashboard admin beserta total keuangan dan grafik 30 hari terakhir.
    public function index()
    {
        // 1. Statistik Utama
        $totalIncome = Transaction::where('status', 'completed')->sum('total_amount');
        $totalExpense = Expense::sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        // 2. Data Grafik (30 Hari Terakhir)
        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Mengambil data transaksi harian
        $dailyTransactions = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        // Menyiapkan array data untuk Chart.js (memastikan setiap hari ada datanya meski 0)
        $chartDates = [];
        $chartIncome = [];

        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays(29 - $i)->format('Y-m-d');
            $displayDate = Carbon::now()->subDays(29 - $i)->format('d M');

            $chartDates[] = $displayDate;
            // Jika ada transaksi hari itu ambil totalnya, jika tidak 0
            $chartIncome[] = isset($dailyTransactions[$date]) ? $dailyTransactions[$date]->total : 0;
        }

        return view('admin.dashboard', compact(
            'totalIncome',
            'totalExpense',
            'netProfit',
            'chartDates',
            'chartIncome'
        ));
    }
}
