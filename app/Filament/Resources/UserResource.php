<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('global_access')
                            ->label('')
                            ->content(function ($record) {
                                if ($record && $record->hasRole(['admin'])) {
                                    return new \Illuminate\Support\HtmlString('<div class="rounded-xl bg-success-50 p-4 ring-1 ring-success-600/20 dark:bg-success-500/10 dark:ring-success-400/30 text-success-700 dark:text-success-400"><strong class="font-bold text-sm">🟢 Global Admin Access:</strong> <span class="text-sm">This user automatically has full implicit access to view and manage all projects system-wide.</span></div>');
                                }
                                if ($record && $record->hasRole(['infra_admin'])) {
                                    return new \Illuminate\Support\HtmlString('<div class="rounded-xl bg-success-50 p-4 ring-1 ring-success-600/20 dark:bg-success-500/10 dark:ring-success-400/30 text-success-700 dark:text-success-400"><strong class="font-bold text-sm">🟢 Infra Admin Access:</strong> <span class="text-sm">This user automatically has edit-level access to all projects system-wide.</span></div>');
                                }
                                return null;
                            })
                            ->hidden(fn ($record) => !($record && $record->hasRole(['admin', 'infra_admin'])))
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Forms\Components\TextInput::make('import_uid')
                    ->label('Import UID')
                    ->readOnly()
                    ->visible(fn ($record) => $record !== null),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
