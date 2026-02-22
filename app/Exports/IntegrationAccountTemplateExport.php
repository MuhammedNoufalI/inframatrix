<?php
namespace App\Exports;

use App\Models\Account;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IntegrationAccountTemplateExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $selectedColumns;

    public function __construct(array $selectedColumns = ['import_uid', 'integration_type_name', 'account_name', 'notes'])
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
        return Account::with('integrationType')->get();
    }

    public function map($account): array
    {
        $row = [];
        foreach ($this->selectedColumns as $column) {
            switch ($column) {
                case 'import_uid':
                    $row[] = $account->import_uid;
                    break;
                case 'integration_type_name':
                    $row[] = $account->integrationType ? $account->integrationType->name : '';
                    break;
                case 'account_name':
                    $row[] = $account->account_name;
                    break;
                case 'notes':
                    $row[] = $account->notes;
                    break;
                default:
                    $row[] = '';
            }
        }
        return $row;
    }
}
