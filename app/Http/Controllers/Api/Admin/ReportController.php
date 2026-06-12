<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\FinancialReportExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private function buildReportData(Carbon $startDate, Carbon $endDate): array
    {
        $incomes = Transaction::with('details.product')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();

        $expenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $topProducts = $incomes
            ->flatMap(function ($transaction) {
                return $transaction->details->map(function ($detail) use ($transaction) {
                    return [
                        'product_id' => $detail->product_id,
                        'product_name' => $detail->product?->name ?? 'Produk tidak ditemukan',
                        'quantity' => (int) $detail->quantity,
                        'subtotal' => (int) $detail->subtotal,
                        'transaction_time' => $transaction->created_at,
                    ];
                });
            })
            ->groupBy('product_id')
            ->map(function ($items) {
                $latest = $items->sortByDesc('transaction_time')->first();

                return [
                    'product_name' => $latest['product_name'],
                    'quantity' => $items->sum('quantity'),
                    'subtotal' => $items->sum('subtotal'),
                    'last_transaction_at' => $latest['transaction_time'],
                ];
            })
            ->sortByDesc('subtotal')
            ->take(10)
            ->values();

        $paymentMethods = $incomes
            ->groupBy('payment_method')
            ->map(function ($items, $method) {
                return [
                    'method' => $method,
                    'count' => $items->count(),
                    'total' => $items->sum('total_amount'),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $topExpenses = $expenses
            ->sortByDesc('amount')
            ->take(10)
            ->values();

        $dailySales = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $salesTrend = collect();
        $periodStart = $startDate->copy()->startOfDay();
        $periodEnd = $endDate->copy()->startOfDay();
        $days = (int) $periodStart->diffInDays($periodEnd);

        for ($i = 0; $i <= $days; $i++) {
            $date = $periodStart->copy()->addDays($i);
            $dateKey = $date->format('Y-m-d');

            $salesTrend->push([
                'date' => $date->format('d/m/Y'),
                'total' => (int) ($dailySales[$dateKey]->total ?? 0),
            ]);
        }

        $totalIncome = $incomes->sum('total_amount');
        $totalExpense = $expenses->sum('amount');

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'incomes' => $incomes,
            'expenses' => $expenses,
            'topProducts' => $topProducts,
            'paymentMethods' => $paymentMethods,
            'topExpenses' => $topExpenses,
            'salesTrend' => $salesTrend,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netProfit' => $totalIncome - $totalExpense,
        ];
    }

    /**
     * Show the report filter page.
     */
    public function index()
    {
        return view('admin.reports');
    }

    /**
     * Generate and download the Excel report.
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $fileName = 'Laporan_Keuangan_' . $startDate->format('dmY') . '-' . $endDate->format('dmY') . '.xlsx';

        return Excel::download(new FinancialReportExport($this->buildReportData($startDate, $endDate)), $fileName);
    }
    /**
     * Preview the report (return HTML format).
     */
    public function preview(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        return view('components.admin.reports_preview', $this->buildReportData($startDate, $endDate))->render();
    }
}
