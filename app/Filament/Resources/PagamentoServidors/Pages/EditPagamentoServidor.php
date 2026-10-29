<?php

namespace App\Filament\Resources\PagamentoServidors\Pages;

use App\Filament\Resources\PagamentoServidors\PagamentoServidorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPagamentoServidor extends EditRecord
{
    protected static string $resource = PagamentoServidorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
