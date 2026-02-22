<?php
namespace App\Exports;

use App\Models\IntegrationType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IntegrationTypeTemplateExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $selectedColumns;

    public function __construct(array $selectedColumns = ['import_uid', 'name', 'behavior', 'is_active'])
    {
        // Force import_uid as the absolute first column
        $selectedColumns = array_values(array_diff($selectedColumns, ['import_uid']));
        array_unshift($selectedColumns, 'import_uid');
        $this->selectedColumns = $selectedColumns;
    }

    public function headings(): array
    {
        return array_map(function($col) {
            return $col === 'import_uid' ? 'import_uid (DO NOT MODIFY)' : $col;
        }, $this->selectedColumns);
    }

    public function collection()
    {
        return IntegrationType::all();
    }

    public function map($integrationType): array
    {
        $row = [];
        foreach ($this->selectedColumns as $column) {
            switch ($column) {
                case 'import_uid':
                    $row[] = $integrationType->import_uid;
                    break;
                case 'name':
                    $row[] = $integrationType->name;
                    break;
                case 'behavior':
                    $row[] = $integrationType->behavior;
                    break;
                case 'is_active':
                    $row[] = $integrationType->is_active ? 'yes' : 'no';
                    break;
                default:
                    $row[] = '';
            }
        }
        return $row;
    }
}
