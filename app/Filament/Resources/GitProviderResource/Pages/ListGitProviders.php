<?php
namespace App\Filament\Resources\GitProviderResource\Pages;
use App\Filament\Resources\GitProviderResource;
use Filament\Resources\Pages\ListRecords;
class ListGitProviders extends ListRecords
{
    protected static string $resource = GitProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
