<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServerResource\Pages;
use App\Filament\Resources\ServerResource\RelationManagers;
use App\Models\Server;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationGroup = 'Master Data';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['admin', 'infra_admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('server_name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('subscription_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\Select::make('provider')
                    ->options(function () {
                        $existing = \App\Models\Server::query()
                            ->select('provider')
                            ->distinct()
                            ->whereNotNull('provider')
                            ->pluck('provider', 'provider')
                            ->toArray();
                        
                        return array_merge([
                            'Azure' => 'Azure',
                            'AWS' => 'AWS',
                            'Contabo' => 'Contabo',
                            'On-prem' => 'On-prem',
                            'Other' => 'Other',
                        ], $existing);
                    })
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('provider_name')
                            ->label('New Provider Name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return $data['provider_name'];
                    })
                    ->required(),
                Forms\Components\Select::make('panel')
                    ->options(function () {
                        $existing = \App\Models\Server::query()
                            ->select('panel')
                            ->distinct()
                            ->whereNotNull('panel')
                            ->pluck('panel', 'panel')
                            ->toArray();

                        return array_merge([
                            'CloudPanel' => 'CloudPanel',
                            'Plesk' => 'Plesk',
                            'None' => 'None',
                        ], $existing);
                    })
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('panel_name')
                            ->label('New Panel Name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return $data['panel_name'];
                    })
                    ->required(),
                Forms\Components\TextInput::make('os_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('os_version')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'maintenance' => 'Maintenance',
                        'decommissioned' => 'Decommissioned',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\Toggle::make('amc')
                    ->required(),
                Forms\Components\TextInput::make('public_ip')
                    ->ip(),
                Forms\Components\TextInput::make('private_ip')
                    ->ip(),
                Forms\Components\TextInput::make('import_uid')
                    ->label('Import UID')
                    ->readOnly()
                    ->helperText('Stable identifier for Excel imports. Do not modify.')
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('server_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider')
                    ->searchable(),
                Tables\Columns\TextColumn::make('public_ip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'maintenance' => 'warning',
                        'decommissioned' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')
                    ->options(function () {
                        return \App\Models\Server::query()
                            ->select('provider')
                            ->distinct()
                            ->whereNotNull('provider')
                            ->pluck('provider', 'provider')
                            ->toArray();
                    }),
                Tables\Filters\SelectFilter::make('panel')
                    ->options(function () {
                        return \App\Models\Server::query()
                            ->select('panel')
                            ->distinct()
                            ->whereNotNull('panel')
                            ->pluck('panel', 'panel')
                            ->toArray();
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'maintenance' => 'Maintenance',
                        'decommissioned' => 'Decommissioned',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Server $record) {
                        $usageCount = \App\Models\Environment::where('server_id', $record->id)->count();
                        if ($usageCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title("Cannot delete {$record->server_name}: used by {$usageCount} environment(s).")
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, \Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                $usageCount = \App\Models\Environment::where('server_id', $record->id)->count();
                                if ($usageCount > 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title("Cannot delete {$record->server_name}: used by {$usageCount} environment(s).")
                                        ->body('Operation halted.')
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServers::route('/'),
            'create' => Pages\CreateServer::route('/create'),
            'edit' => Pages\EditServer::route('/{record}/edit'),
        ];
    }
}
