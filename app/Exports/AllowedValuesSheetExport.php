<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllowedValuesSheetExport implements FromArray, WithTitle, WithHeadings
{
    protected array $data;
    protected array $headings;

    public function __construct(array $headings, array $data)
    {
        $this->headings = $headings;
        
        // Pad the arrays to be equal length for vertical columnar display
        $maxRows = 0;
        foreach ($data as $columnData) {
            if (count($columnData) > $maxRows) {
                $maxRows = count($columnData);
            }
        }

        $formattedData = [];
        for ($i = 0; $i < $maxRows; $i++) {
            $row = [];
            foreach ($data as $columnData) {
                $row[] = $columnData[$i] ?? '';
            }
            $formattedData[] = $row;
        }

        $this->data = $formattedData;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'Allowed Values';
    }
}
