<?php
namespace App\Exports;

use App\Models\Project;
use App\Models\Server;
use App\Models\GitProvider;
use App\Models\IntegrationType;
use App\Models\Account;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProjectTemplateExport implements WithMultipleSheets
{
    protected array $selectedColumns;

    public function __construct(array $selectedColumns = ['project_import_uid', 'environment_import_uid', 'project_name', 'project_status', 'project_notes', 'environment_type', 'environment_url', 'server_name', 'git_provider_name', 'repo_url', 'repo_branch', 'cicd_configured', 'cicd_reason', 'integration_type_name', 'integration_account_name', 'integration_identifier', 'assignment_user_email', 'assignment_role'])
    {
        // Force UUIDs to the front
        $selectedColumns = array_values(array_diff($selectedColumns, ['project_import_uid', 'environment_import_uid']));
        array_unshift($selectedColumns, 'project_import_uid', 'environment_import_uid');
        $this->selectedColumns = $selectedColumns;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Real Data Export
        $sheets[] = new class($this->selectedColumns) implements FromArray, WithHeadings, WithTitle {
            protected array $selectedColumns;

            public function __construct(array $selectedColumns)
            {
                $this->selectedColumns = $selectedColumns;
            }

            public function headings(): array
            {
                return array_map(function($col) {
                    if ($col === 'project_import_uid') return 'project_import_uid (DO NOT MODIFY)';
                    if ($col === 'environment_import_uid') return 'environment_import_uid (DO NOT MODIFY)';
                    return $col;
                }, $this->selectedColumns);
            }

            public function array(): array
            {
                $projects = Project::with([
                    'environments.server',
                    'environments.gitProvider',
                    'environments.integrations.integrationType',
                    'environments.integrations.account',
                    'users'
                ])->get();

                $rows = [];

                foreach ($projects as $project) {
                    $envs = $project->environments->isEmpty() ? collect([null]) : $project->environments;
                    $assignments = $project->users->isEmpty() ? collect([null]) : $project->users;

                    foreach ($envs as $env) {
                        $integrations = ($env && $env->integrations->isNotEmpty()) ? $env->integrations : collect([null]);
                        
                        foreach ($integrations as $integration) {
                            foreach ($assignments as $assignment) {
                                $sourceData = [
                                    'project_import_uid' => $project->import_uid,
                                    'environment_import_uid' => $env ? $env->import_uid : '',
                                    'project_name' => $project->name,
                                    'project_status' => $project->status,
                                    'project_notes' => $project->notes,
                                    'environment_type' => $env ? $env->type : '',
                                    'environment_url' => $env ? $env->url : '',
                                    'server_name' => ($env && $env->server) ? $env->server->server_name : '',
                                    'git_provider_name' => ($env && $env->gitProvider) ? $env->gitProvider->name : '',
                                    'repo_url' => $env ? $env->repo_url : '',
                                    'repo_branch' => $env ? $env->repo_branch : '',
                                    'cicd_configured' => $env ? ($env->cicd_configured ? 'yes' : 'no') : '',
                                    'cicd_reason' => $env ? $env->cicd_not_configured_reason : '',
                                    'integration_type_name' => ($integration && $integration->integrationType) ? $integration->integrationType->name : '',
                                    'integration_account_name' => ($integration && $integration->account) ? $integration->account->account_name : '',
                                    'integration_identifier' => $integration ? $integration->value : '',
                                    'assignment_user_email' => $assignment ? $assignment->email : '',
                                    'assignment_role' => $assignment ? $assignment->pivot->role : '',
                                ];

                                $row = [];
                                foreach ($this->selectedColumns as $column) {
                                    $row[] = $sourceData[$column] ?? '';
                                }
                                $rows[] = $row;
                            }
                        }
                    }
                }

                return $rows;
            }

            public function title(): string
            {
                return 'Projects Full';
            }
        };

        // Sheet 2: Allowed Values
        $statuses = ['active', 'on_hold', 'archived'];
        $envTypes = ['staging', 'uat', 'live'];
        $servers = Server::pluck('server_name')->toArray();
        $gitProviders = GitProvider::pluck('name')->toArray();
        $booleans = ['yes', 'no'];
        $integrationTypes = IntegrationType::pluck('name')->toArray();
        $accounts = Account::pluck('account_name')->toArray();
        $users = User::pluck('email')->toArray();
        $roles = ['owner', 'editor', 'viewer'];

        $sheets[] = new AllowedValuesSheetExport(
            [
                'project_status', 'environment_type', 'server_name', 'git_provider_name', 
                'cicd_configured', 'integration_type_name', 'integration_account_name', 
                'assignment_user_email', 'assignment_role'
            ],
            [
                $statuses, $envTypes, $servers, $gitProviders, 
                $booleans, $integrationTypes, $accounts, 
                $users, $roles
            ]
        );

        return $sheets;
    }
}
