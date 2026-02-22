<?php
namespace App\Exports;

use App\Models\Server;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ServerTemplateExport implements WithMultipleSheets
{
    protected array $selectedColumns;

    public function __construct(array $selectedColumns = ['import_uid', 'server_name', 'subscription_name', 'location', 'provider', 'panel', 'os_name', 'os_version', 'public_ip', 'private_ip', 'status', 'amc', 'is_active'])
    {
        // Force import_uid as the absolute first column
        $selectedColumns = array_values(array_diff($selectedColumns, ['import_uid']));
        array_unshift($selectedColumns, 'import_uid');
        $this->selectedColumns = $selectedColumns;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Real Data Export
        $sheets[] = new class($this->selectedColumns) implements FromCollection, WithHeadings, WithMapping, WithTitle {
            protected array $selectedColumns;

            public function __construct(array $selectedColumns)
            {
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
                return Server::all();
            }

            public function map($server): array
            {
                $row = [];
                foreach ($this->selectedColumns as $column) {
                    switch ($column) {
                        case 'import_uid': $row[] = $server->import_uid; break;
                        case 'server_name': $row[] = $server->server_name; break;
                        case 'subscription_name': $row[] = $server->subscription_name; break;
                        case 'location': $row[] = $server->location; break;
                        case 'provider': $row[] = $server->provider; break;
                        case 'panel': $row[] = $server->panel; break;
                        case 'os_name': $row[] = $server->os_name; break;
                        case 'os_version': $row[] = $server->os_version; break;
                        case 'public_ip': $row[] = $server->public_ip; break;
                        case 'private_ip': $row[] = $server->private_ip; break;
                        case 'status': $row[] = $server->status; break;
                        case 'amc': $row[] = $server->amc ? 'yes' : 'no'; break;
                        case 'is_active': $row[] = $server->is_active ? 'yes' : 'no'; break;
                        default: $row[] = '';
                    }
                }
                return $row;
            }

            public function title(): string
            {
                return 'Servers';
            }
        };

        // Sheet 2: Allowed Values
        $dynamicProviders = Server::whereNotNull('provider')->pluck('provider')->unique()->toArray();
        $providers = array_values(array_unique(array_merge(['Azure', 'AWS', 'Contabo', 'On-prem', 'Other'], $dynamicProviders)));
        
        $dynamicPanels = Server::whereNotNull('panel')->pluck('panel')->unique()->toArray();
        $panels = array_values(array_unique(array_merge(['CloudPanel', 'Plesk', 'None'], $dynamicPanels)));
        
        $statuses = ['active', 'maintenance', 'decommissioned'];
        $booleans = ['yes', 'no'];

        $sheets[] = new AllowedValuesSheetExport(
            ['provider', 'panel', 'status', 'amc'],
            [$providers, $panels, $statuses, $booleans]
        );

        return $sheets;
    }
}
