<?php

namespace App\Filament\Resources\Secretarias\Pages;

use App\Filament\Resources\Secretarias\SecretariaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSecretarias extends ListRecords
{
    protected static string $resource = SecretariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
