<?php
namespace App\Filament\Resources\InviteResource\Pages;
use App\Filament\Resources\InviteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateInvite extends CreateRecord 
{ 
    protected static string $resource = InviteResource::class; 

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['token'] = Str::random(32);
        $data['expires_at'] = now()->addDays(7);
        return $data;
    }
}
