<?php
namespace App\Filament\Resources\InviteResource\Pages;
use App\Filament\Resources\InviteResource;
use Filament\Resources\Pages\ListRecords;
class ListInvites extends ListRecords
{
    protected static string $resource = InviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
