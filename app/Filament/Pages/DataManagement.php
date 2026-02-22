<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

// Imports
use App\Imports\IntegrationTypeImport;
use App\Imports\IntegrationAccountImport;
use App\Imports\ServerImport;
use App\Imports\UserImport;
use App\Imports\ProjectImport;

// Exports
use App\Exports\IntegrationTypeTemplateExport;
use App\Exports\IntegrationAccountTemplateExport;
use App\Exports\ServerTemplateExport;
use App\Exports\UserTemplateExport;
use App\Exports\ProjectTemplateExport;
use App\Exports\ErrorReportExport;

class DataManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static string $view = 'filament.pages.data-management';

    protected static ?string $navigationGroup = 'Settings';

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('download_integration_template')
                    ->label('Integration Types')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('columns')
                            ->label('Select Fields to Export')
                            ->options(['import_uid' => 'import_uid (DO NOT MODIFY)', 'name' => 'Name (Required)', 'behavior' => 'Behavior', 'is_active' => 'Is Active'])
                            ->default(['import_uid', 'behavior', 'is_active'])
                            ->disableOptionWhen(fn (string $value): bool => $value === 'import_uid')
                            ->columns(2),
                    ])
                    ->action(function (array $data) {
                        return Excel::download(new IntegrationTypeTemplateExport(array_unique(array_merge(['name'], $data['columns'] ?? []))), 'integration_types_export.xlsx');
                    }),

                Action::make('download_integration_account_template')
                    ->label('Integration Accounts')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('columns')
                            ->label('Select Fields to Export')
                            ->options(['import_uid' => 'import_uid (DO NOT MODIFY)', 'integration_type_name' => 'Type Name (Required)', 'account_name' => 'Account Name (Required)', 'notes' => 'Notes'])
                            ->default(['import_uid', 'notes'])
                            ->disableOptionWhen(fn (string $value): bool => $value === 'import_uid')
                            ->columns(2),
                    ])
                    ->action(function (array $data) {
                        return Excel::download(new IntegrationAccountTemplateExport(array_unique(array_merge(['integration_type_name', 'account_name'], $data['columns'] ?? []))), 'integration_accounts_export.xlsx');
                    }),

                Action::make('download_server_template')
                    ->label('Servers')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('columns')
                            ->label('Select Fields to Export')
                            ->options([
                                'import_uid' => 'import_uid (DO NOT MODIFY)',
                                'server_name' => 'Server Name (Required)', 'subscription_name' => 'Subscription', 'location' => 'Location',
                                'provider' => 'Provider', 'panel' => 'Panel', 'os_name' => 'OS Name', 'os_version' => 'OS Version',
                                'public_ip' => 'Public IP', 'private_ip' => 'Private IP', 'status' => 'Status', 'amc' => 'AMC', 'is_active' => 'Is Active'
                            ])
                            ->default(['import_uid', 'subscription_name', 'location', 'provider', 'panel', 'os_name', 'os_version', 'public_ip', 'private_ip', 'status', 'amc', 'is_active'])
                            ->disableOptionWhen(fn (string $value): bool => $value === 'import_uid')
                            ->columns(3),
                    ])
                    ->action(function (array $data) {
                        return Excel::download(new ServerTemplateExport(array_unique(array_merge(['server_name'], $data['columns'] ?? []))), 'servers_export.xlsx');
                    }),

                Action::make('download_user_template')
                    ->label('Users')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('columns')
                            ->label('Select Fields to Export')
                            ->options(['import_uid' => 'import_uid (DO NOT MODIFY)', 'email' => 'Email (Required)', 'name' => 'Name', 'role' => 'System Role'])
                            ->default(['import_uid', 'name', 'role'])
                            ->disableOptionWhen(fn (string $value): bool => $value === 'import_uid')
                            ->columns(2),
                    ])
                    ->action(function (array $data) {
                        return Excel::download(new UserTemplateExport(array_unique(array_merge(['email'], $data['columns'] ?? []))), 'users_export.xlsx');
                    }),

                Action::make('download_project_template')
                    ->label('Projects Full')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('columns')
                            ->label('Select Fields to Export')
                            ->options([
                                'project_import_uid' => 'project_import_uid (DO NOT MODIFY)',
                                'environment_import_uid' => 'environment_import_uid (DO NOT MODIFY)',
                                'project_name' => 'Project Name (Required)', 'project_status' => 'Project Status', 'project_notes' => 'Project Notes',
                                'environment_type' => 'Environment Type', 'environment_url' => 'Environment URL', 'server_name' => 'Server Name',
                                'git_provider_name' => 'Git Provider Name', 'repo_url' => 'Repo URL', 'repo_branch' => 'Repo Branch',
                                'cicd_configured' => 'CI/CD Configured', 'cicd_reason' => 'CI/CD Reason', 'integration_type_name' => 'Integration Type Name',
                                'integration_account_name' => 'Integration Account Name', 'integration_identifier' => 'Integration Identifier',
                                'assignment_user_email' => 'Assignment User Email', 'assignment_role' => 'Assignment Role'
                            ])
                            ->default([
                                'project_import_uid', 'environment_import_uid',
                                'project_status', 'project_notes', 'environment_type', 'environment_url', 'server_name', 'git_provider_name',
                                'repo_url', 'repo_branch', 'cicd_configured', 'cicd_reason', 'integration_type_name', 'integration_account_name',
                                'integration_identifier', 'assignment_user_email', 'assignment_role'
                            ])
                            ->disableOptionWhen(fn (string $value): bool => in_array($value, ['project_import_uid', 'environment_import_uid']))
                            ->columns(3),
                    ])
                    ->action(function (array $data) {
                        return Excel::download(new ProjectTemplateExport(array_unique(array_merge(['project_name'], $data['columns'] ?? []))), 'projects_full_export.xlsx');
                    }),
            ])
            ->label('Download Data')
            ->icon('heroicon-o-arrow-down-tray')
            ->button(),

            ActionGroup::make([
                $this->buildImportAction('importIntegrationTypes', 'Import Integration Types', new IntegrationTypeImport),
                $this->buildImportAction('importIntegrationAccounts', 'Import Integration Accounts', new IntegrationAccountImport),
                $this->buildImportAction('importServers', 'Import Servers', new ServerImport),
                $this->buildImportAction('importUsers', 'Import Users', new UserImport),
                $this->buildImportAction('importProjects', 'Import Projects (Full Hierarchy)', new ProjectImport),
            ])
            ->label('Process Uploads')
            ->icon('heroicon-o-arrow-up-tray')
            ->button()
            ->color('success'),
        ];
    }

    private function buildImportAction(string $name, string $label, $importClass): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon('heroicon-o-cloud-arrow-up')
            ->form([
                FileUpload::make('file')
                    ->label('Excel File')
                    ->disk('local')
                    ->directory('imports')
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                    ->required(),
            ])
            ->action(function (array $data) use ($importClass, $label) {
                $filePath = Storage::disk('local')->path($data['file']);
                
                try {
                    Excel::import($importClass, $filePath);
                    
                    $notification = Notification::make()
                        ->title("{$label} Completed")
                        ->body("Created: {$importClass->createdCount} | Updated: {$importClass->updatedCount} | Skipped: {$importClass->skippedCount} | Failed: {$importClass->failedCount}")
                        ->success()
                        ->persistent();

                    if ($importClass->failedCount > 0 && !empty($importClass->errors)) {
                        $fileName = 'error_reports/import_errors_' . now()->format('YmdHis') . '.xlsx';
                        Excel::store(new ErrorReportExport($importClass->errors), $fileName, 'public');

                        $notification->actions([
                            \Filament\Notifications\Actions\Action::make('download_errors')
                                ->label('Download Error Report')
                                ->button()
                                ->color('danger')
                                ->url(Storage::disk('public')->url($fileName), shouldOpenInNewTab: true),
                        ]);
                    }

                    $notification->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }

                // Cleanup
                if (Storage::disk('local')->exists($data['file'])) {
                    Storage::disk('local')->delete($data['file']);
                }
            });
    }
}
