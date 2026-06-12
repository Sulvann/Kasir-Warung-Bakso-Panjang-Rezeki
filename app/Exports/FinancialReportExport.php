<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinancialReportExport implements WithMultipleSheets
{
    private const HEADER_BLUE = 'D9EAF7';
    private const HEADER_GREEN = 'DFF5E8';
    private const HEADER_RED = 'FDE2E2';

    public function __construct(private array $data)
    {
    }

    public function sheets(): array
    {
        $incomeSheet = $this->incomeSheet();
        $expenseSheet = $this->expenseSheet();

        return [
            new ReportSheet('Ringkasan', $this->summaryRows(), [1, 2, 4], [4 => self::HEADER_BLUE]),
            new ReportSheet('Pemasukan', $incomeSheet['rows'], $incomeSheet['boldRows'], $incomeSheet['fillRows']),
            new ReportSheet('Pengeluaran', $expenseSheet['rows'], $expenseSheet['boldRows'], $expenseSheet['fillRows']),
            new ReportSheet('Produk Terbesar', $this->topProductRows(), [1], [1 => self::HEADER_BLUE]),
            new ReportSheet('Metode Bayar', $this->paymentMethodRows(), [1], [1 => self::HEADER_BLUE]),
            new ReportSheet('Pengeluaran Terbesar', $this->topExpenseRows(), [1], [1 => self::HEADER_RED]),
            new ReportSheet('Tren Penjualan', $this->salesTrendRows(), [1], [1 => self::HEADER_BLUE]),
        ];
    }

    private function summaryRows(): array
    {
        return [
            ['WARUNG BAKSO PANJANG REZEKI'],
            ['Laporan Keuangan Periode', $this->periodText()],
            [],
            ['Keterangan', 'Nominal'],
            ['Total Pemasukan', $this->money($this->data['totalIncome'])],
            ['Total Pengeluaran', $this->money($this->data['totalExpense'])],
            ['Laba Bersih', $this->money($this->data['netProfit'])],
        ];
    }

    private function incomeSheet(): array
    {
        $rows = [];
        $boldRows = [];
        $fillRows = [];

        $groupedIncomes = $this->data['incomes']->groupBy(fn ($income) => $income->created_at->format('Y-m-d'));

        if ($groupedIncomes->isEmpty()) {
            return [
                'rows' => [['Tidak ada data pemasukan pada periode ini.']],
                'boldRows' => [1],
                'fillRows' => [1 => self::HEADER_GREEN],
            ];
        }

        foreach ($groupedIncomes as $date => $incomes) {
            if (!empty($rows)) {
                $rows[] = [''];
            }

            $rows[] = ['Tanggal', Carbon::parse($date)->translatedFormat('l, d/m/Y')];
            $boldRows[] = count($rows);

            $rows[] = ['No', 'Nama Pelanggan', 'Nomor Telepon', 'Waktu', 'Metode', 'Total'];
            $boldRows[] = count($rows);
            $fillRows[count($rows)] = self::HEADER_GREEN;

            foreach ($incomes->values() as $index => $income) {
                $rows[] = [
                    $index + 1,
                    $income->customer_name ?: '-',
                    $income->phone_number ?: '-',
                    $income->created_at->format('H:i'),
                    $this->paymentLabel($income->payment_method),
                    $this->money($income->total_amount),
                ];
            }

            $rows[] = ['', '', '', '', 'Subtotal Harian', $this->money($incomes->sum('total_amount'))];
            $boldRows[] = count($rows);
        }

        $rows[] = [''];
        $rows[] = ['', '', '', '', 'Total Pemasukan', $this->money($this->data['totalIncome'])];
        $boldRows[] = count($rows);

        return [
            'rows' => $rows,
            'boldRows' => $boldRows,
            'fillRows' => $fillRows,
        ];
    }

    private function expenseSheet(): array
    {
        $rows = [];
        $boldRows = [];
        $fillRows = [];

        $groupedExpenses = $this->data['expenses']->groupBy(fn ($expense) => $expense->created_at->format('Y-m-d'));

        if ($groupedExpenses->isEmpty()) {
            return [
                'rows' => [['Tidak ada data pengeluaran pada periode ini.']],
                'boldRows' => [1],
                'fillRows' => [1 => self::HEADER_RED],
            ];
        }

        foreach ($groupedExpenses as $date => $expenses) {
            if (!empty($rows)) {
                $rows[] = [''];
            }

            $rows[] = ['Tanggal', Carbon::parse($date)->translatedFormat('l, d/m/Y')];
            $boldRows[] = count($rows);

            $rows[] = ['No', 'Waktu', 'Kategori', 'Keterangan', 'Jumlah'];
            $boldRows[] = count($rows);
            $fillRows[count($rows)] = self::HEADER_RED;

            foreach ($expenses->values() as $index => $expense) {
                $rows[] = [
                    $index + 1,
                    $expense->created_at->format('H:i'),
                    $this->expenseCategoryLabel($expense->category),
                    $expense->description,
                    $this->money($expense->amount),
                ];
            }

            $rows[] = ['', '', '', 'Subtotal Harian', $this->money($expenses->sum('amount'))];
            $boldRows[] = count($rows);
        }

        $rows[] = [''];
        $rows[] = ['', '', '', 'Total Pengeluaran', $this->money($this->data['totalExpense'])];
        $boldRows[] = count($rows);

        return [
            'rows' => $rows,
            'boldRows' => $boldRows,
            'fillRows' => $fillRows,
        ];
    }

    private function topProductRows(): array
    {
        $rows = [['No', 'Produk', 'Total Dibeli', 'Terakhir Dibeli', 'Total Pemasukan']];

        foreach ($this->data['topProducts'] as $index => $product) {
            $rows[] = [
                $index + 1,
                $product['product_name'],
                $product['quantity'],
                $this->dateTime($product['last_transaction_at']),
                $this->money($product['subtotal']),
            ];
        }

        return $rows;
    }

    private function paymentMethodRows(): array
    {
        $rows = [['No', 'Metode', 'Jumlah Transaksi', 'Total Nominal']];

        foreach ($this->data['paymentMethods'] as $index => $method) {
            $rows[] = [
                $index + 1,
                $this->paymentLabel($method['method']),
                $method['count'],
                $this->money($method['total']),
            ];
        }

        return $rows;
    }

    private function topExpenseRows(): array
    {
        $rows = [['No', 'Tanggal', 'Kategori', 'Keterangan', 'Jumlah']];

        foreach ($this->data['topExpenses'] as $index => $expense) {
            $rows[] = [
                $index + 1,
                $this->dateTime($expense->created_at),
                $this->expenseCategoryLabel($expense->category),
                $expense->description,
                $this->money($expense->amount),
            ];
        }

        return $rows;
    }

    private function salesTrendRows(): array
    {
        $rows = [['No', 'Tanggal', 'Total Penjualan']];

        foreach ($this->data['salesTrend'] as $index => $trend) {
            $rows[] = [
                $index + 1,
                $trend['date'],
                $this->money($trend['total']),
            ];
        }

        return $rows;
    }

    private function periodText(): string
    {
        return $this->data['startDate']->format('d/m/Y') . ' - ' . $this->data['endDate']->format('d/m/Y');
    }

    private function dateTime(Carbon $date): string
    {
        return $date->translatedFormat('l, d/m/Y H:i');
    }

    private function money(int|float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    private function paymentLabel(?string $method): string
    {
        return strtolower((string) $method) === 'cash' ? 'Tunai' : strtoupper((string) $method);
    }

    private function expenseCategoryLabel(?string $category): string
    {
        return match (strtolower((string) $category)) {
            'ingredient' => 'Bahan',
            'operational' => 'Operasional',
            'others' => 'Lain-lain',
            default => ucfirst((string) $category),
        };
    }
}
