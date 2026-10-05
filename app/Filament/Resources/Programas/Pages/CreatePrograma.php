<?php

namespace App\Filament\Resources\Programas\Pages;

use App\Enums\StatusPrograma;
use App\Filament\Resources\Programas\ProgramaResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrograma extends CreateRecord
{
    protected static string $resource = ProgramaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            $data['secretaria_id'] = $user->secretaria_id;
        }

        $data['status'] = StatusPrograma::Rascunho;

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Programa salvo como rascunho. Quando terminar, clique em "Enviar para revisão".';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
