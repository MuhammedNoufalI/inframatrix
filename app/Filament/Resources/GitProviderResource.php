<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GitProviderResource\Pages;
use App\Models\GitProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GitProviderResource extends Resource
{
    protected static ?string $model = GitProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';



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
                Forms\Components\TextInput::make('base_url')
                    ->url()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('base_url')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGitProviders::route('/'),
            'create' => Pages\CreateGitProvider::route('/create'),
            'edit' => Pages\EditGitProvider::route('/{record}/edit'),
        ];
    }
}
