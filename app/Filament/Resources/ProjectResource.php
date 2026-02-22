<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Project Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Project Details')
                    ->tabs([
                        Tabs\Tab::make('Overview')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'on_hold' => 'On Hold',
                                        'archived' => 'Archived',
                                    ])
                                    ->required()
                                    ->default('active'),
                                Textarea::make('notes')
                                    ->columnSpanFull(),
                                TextInput::make('import_uid')
                                    ->label('Project Import UID')
                                    ->readOnly()
                                    ->visible(fn ($record) => $record !== null),
                            ]),
                        Tabs\Tab::make('Environments')
                            ->schema([
                                Forms\Components\Repeater::make('environments')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->options([
                                                'staging' => 'Staging',
                                                'uat' => 'UAT',
                                                'live' => 'Live',
                                            ])
                                            ->required()
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                        Forms\Components\TextInput::make('url')
                                            ->label('URL')
                                            ->url()
                                            ->required(),
                                        Forms\Components\Select::make('server_id')
                                            ->relationship('server', 'server_name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Forms\Components\Select::make('git_provider_id')
                                            ->relationship('gitProvider', 'name')
                                            ->required(),
                                        Forms\Components\TextInput::make('repo_url')
                                            ->required(),
                                        Forms\Components\TextInput::make('repo_branch')
                                            ->required(),
                                        Forms\Components\Toggle::make('cicd_configured')
                                            ->reactive(),
                                        Forms\Components\Textarea::make('cicd_not_configured_reason')
                                            ->required(fn (Forms\Get $get) => !$get('cicd_configured'))
                                            ->visible(fn (Forms\Get $get) => !$get('cicd_configured')),
                                        Forms\Components\FileUpload::make('checklist_attachment')
                                            ->visible(fn (Forms\Get $get) => $get('type') === 'live')
                                            ->directory('checklists'),
                                        Forms\Components\TextInput::make('import_uid')
                                            ->label('Environment Import UID')
                                            ->readOnly()
                                            ->visible(fn ($record) => $record !== null),

                                        Forms\Components\Repeater::make('integrations')
                                            ->relationship()
                                            ->defaultItems(0)
                                            ->schema([
                                                Forms\Components\Select::make('integration_type_id')
                                                    ->label('Type')
                                                    ->relationship(
                                                        name: 'integrationType', 
                                                        titleAttribute: 'name', 
                                                        modifyQueryUsing: fn (Builder $query) => $query->where('is_active', true)
                                                    )
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                        // clear fields if type changes
                                                        $set('account_id', null);
                                                        $set('value', null);
                                                    }),
                                                    
                                                Forms\Components\Select::make('account_id')
                                                    ->label('Select Account (Optional)')
                                                    ->options(function (Forms\Get $get) {
                                                        $integrationType = \App\Models\IntegrationType::find($get('integration_type_id'));
                                                        if (!$integrationType) return [];
                                                        return \App\Models\Account::where('integration_type_id', $integrationType->id)->pluck('account_name', 'id');
                                                    })
                                                    ->visible(fn (Forms\Get $get) => \App\Models\IntegrationType::find($get('integration_type_id'))?->behavior === 'account_select_optional')
                                                    ->dehydrateStateUsing(fn ($state) => blank($state) ? null : $state),
                                                    
                                                Forms\Components\TextInput::make('value')
                                                    ->label('Identifier / Config ID')
                                                    ->visible(fn (Forms\Get $get) => in_array(\App\Models\IntegrationType::find($get('integration_type_id'))?->behavior, ['generic_value', 'account_select_optional']))
                                                    ->required(fn (Forms\Get $get) => \App\Models\IntegrationType::find($get('integration_type_id'))?->behavior === 'generic_value')
                                                    ->dehydrateStateUsing(fn ($state) => blank($state) ? null : $state),
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['type'] ?? null)
                                    ->maxItems(3)
                                    ->addActionLabel('Add Environment')
                                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                        return $data;
                                    }),
                            ]),
                        Tabs\Tab::make('Access Summary')
                            ->schema([
                                Forms\Components\ViewField::make('acl_summary')
                                    ->view('filament.forms.components.project-access-summary')
                                    ->columnSpanFull()
                                    ->label('')
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ViewColumn::make('environments')
                    ->label('Environment URLs')
                    ->view('filament.tables.columns.environment-urls')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('environments', function (Builder $q) use ($search) {
                            $q->where('url', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'on_hold' => 'warning',
                        'archived' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('environment_type')
                    ->label('Environment Type')
                    ->multiple()
                    ->options([
                        'staging' => 'Staging',
                        'uat' => 'UAT',
                        'live' => 'Live',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['values'])) return $query;
                        return $query->whereHas('environments', fn (Builder $q) => $q->whereIn('type', $data['values']));
                    }),
                Tables\Filters\SelectFilter::make('server')
                    ->label('Server')
                    ->multiple()
                    ->relationship('environments.server', 'server_name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('git_provider')
                    ->label('Git Provider')
                    ->multiple()
                    ->relationship('environments.gitProvider', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('integration_type')
                    ->label('Integration Type')
                    ->multiple()
                    ->relationship('environments.integrations.integrationType', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('account')
                    ->label('Integration Account')
                    ->multiple()
                    ->relationship('environments.integrations.account', 'account_name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('integration_value')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Integration Identifier / Config ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) return $query;
                        return $query->whereHas('environments.integrations', fn (Builder $q) => $q->where('value', 'like', "%{$data['value']}%"));
                    }),
                Tables\Filters\TernaryFilter::make('cicd_configured')
                    ->label('CI/CD Configured')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('environments', fn (Builder $query) => $query->where('cicd_configured', true)),
                        false: fn (Builder $query) => $query->whereHas('environments', fn (Builder $query) => $query->where('cicd_configured', false)),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'environments:id,project_id,type,url,server_id,git_provider_id', 
                'environments.server:id,server_name', 
                'environments.gitProvider:id,name', 
                'environments.integrations:id,environment_id,integration_type_id,account_id,value',
                'environments.integrations.integrationType:id,name', 
                'environments.integrations.account:id,account_name'
            ]));
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()->hasRole(['admin', 'infra_admin'])) {
            $query->whereHas('users', fn (Builder $q) => $q->where('user_id', auth()->id()));
        }

        return $query;
    }
}
