<?php

namespace App\Filament\Resources\Secretarias\Pages;

use App\Filament\Resources\Secretarias\SecretariaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSecretaria extends EditRecord
{
    protected static string $resource = SecretariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
