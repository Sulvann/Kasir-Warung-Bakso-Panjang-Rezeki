<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    // Menyimpan konfigurasi dasar satu sheet laporan.
    public function __construct(
        private string $title,
        private array $rows,
        private array $boldRows = [],
        private array $fillRows = []
    ) {
    }

    // Mengirim baris data ke package Excel untuk ditulis ke worksheet.
    public function array(): array
    {
        return $this->rows;
    }

    // Menentukan nama tab sheet pada file Excel.
    public function title(): string
    {
        return $this->title;
    }

    // Menerapkan style bold dan warna background pada baris tertentu.
    public function styles(Worksheet $sheet): array
    {
        $styles = [];

        foreach ($this->boldRows as $rowNumber) {
            $styles[$rowNumber] = ['font' => ['bold' => true]];
        }

        foreach ($this->fillRows as $rowNumber => $color) {
            $styles[$rowNumber]['fill'] = [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $color],
            ];
        }

        return $styles;
    }
}
