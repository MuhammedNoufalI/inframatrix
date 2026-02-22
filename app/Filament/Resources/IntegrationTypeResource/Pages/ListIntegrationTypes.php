<?php
namespace App\Filament\Resources\IntegrationTypeResource\Pages;
use App\Filament\Resources\IntegrationTypeResource;
use Filament\Resources\Pages\ListRecords;
class ListIntegrationTypes extends ListRecords
{
    protected static string $resource = IntegrationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
