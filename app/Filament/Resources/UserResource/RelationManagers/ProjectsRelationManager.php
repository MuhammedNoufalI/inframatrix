<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        'owner' => 'Owner',
                        'editor' => 'Editor',
                        'viewer' => 'Viewer',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'on_hold' => 'warning',
                        'archived' => 'gray',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('role')
                    ->label('Assigned Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'owner' => 'danger',
                        'editor' => 'warning',
                        'viewer' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('role')
                            ->options([
                                'owner' => 'Owner',
                                'editor' => 'Editor',
                                'viewer' => 'Viewer',
                            ])
                            ->required()
                            ->default('viewer'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
