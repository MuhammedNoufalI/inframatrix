<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ErrorReportExport implements FromArray, WithHeadings, WithStyles
{
    protected array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function headings(): array
    {
        return ['Row Number', 'Column Name', 'Invalid Excel Value', 'Error Message', 'Suggested / Allowed Values'];
    }

    public function array(): array
    {
        return $this->errors;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFF0000']]],
        ];
    }
}
