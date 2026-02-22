<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class IntegrationAccountUsageWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Integration Accounts Usage')
            ->query(
                \App\Models\Account::query()
                    ->whereNotNull('integration_type_id')
                    ->select('accounts.*')
                    ->selectRaw('(SELECT COUNT(DISTINCT e.project_id) FROM integrations i JOIN environments e ON e.id = i.environment_id WHERE i.account_id = accounts.id) as projects_count')
                    ->with('integrationType')
            )
            ->columns([
                Tables\Columns\TextColumn::make('integrationType.name')
                    ->label('Service')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Account Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('projects_count')
                    ->label('Linked Projects')
                    ->sortable(),
            ])
            ->defaultSort('projects_count', 'desc');
    }
}
