<?php

namespace App\Filament\Resources\PagamentoServidors\Pages;

use App\Filament\Resources\PagamentoServidors\PagamentoServidorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPagamentoServidors extends ListRecords
{
    protected static string $resource = PagamentoServidorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
