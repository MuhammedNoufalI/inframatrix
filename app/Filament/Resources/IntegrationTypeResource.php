<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegrationTypeResource\Pages;
use App\Models\IntegrationType;
use App\Filament\Resources\IntegrationTypeResource\RelationManagers\AccountsRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IntegrationTypeResource extends Resource
{
    protected static ?string $model = IntegrationType::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Master Data';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['admin', 'infra_admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('behavior')
                    ->options([
                        'generic_value' => 'Generic Value / Text',
                        'account_select_optional' => 'Account Select',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\TextInput::make('import_uid')
                    ->label('Import UID')
                    ->readOnly()
                    ->helperText('Stable identifier for Excel imports.')
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('behavior')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, IntegrationType $record) {
                        $usageCount = \App\Models\Integration::where('integration_type_id', $record->id)->count();
                        if ($usageCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title("Cannot delete: used by {$usageCount} projects.")
                                ->body('Archive (disable) this integration type instead to preserve history.')
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
                                $usageCount = \App\Models\Integration::where('integration_type_id', $record->id)->count();
                                if ($usageCount > 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title("Cannot delete {$record->name}: used by {$usageCount} projects.")
                                        ->body('Operation halted. Archive (disable) integration types instead to preserve history.')
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
            AccountsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntegrationTypes::route('/'),
            'create' => Pages\CreateIntegrationType::route('/create'),
            'edit' => Pages\EditIntegrationType::route('/{record}/edit'),
        ];
    }
}
