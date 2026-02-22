<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserTemplateExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $selectedColumns;

    public function __construct(array $selectedColumns = ['import_uid', 'email', 'name', 'role'])
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
        return User::with('roles')->get();
    }

    public function map($user): array
    {
        $roleInfo = $user->roles->first() ? $user->roles->first()->name : '';

        $row = [];
        foreach ($this->selectedColumns as $column) {
            switch ($column) {
                case 'import_uid': $row[] = $user->import_uid; break;
                case 'email': $row[] = $user->email; break;
                case 'name': $row[] = $user->name; break;
                case 'role': $row[] = $roleInfo; break;
                default: $row[] = '';
            }
        }
        return $row;
    }
}
