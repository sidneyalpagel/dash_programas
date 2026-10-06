<?php

namespace App\Filament\Widgets;

use App\Enums\StatusPrograma;
use App\Filament\Resources\Programas\ProgramaResource;
use App\Support\Formato;
use App\Support\Panorama;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumoProgramas extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $programas = ProgramaResource::getEloquentQuery()->get();
        $exibido = Panorama::exercicioExibido();
        // Publicados: só o ano que o site mostra, para não somar exercícios diferentes.
        $publicados = $programas->where('status', StatusPrograma::Publicado)->where('exercicio', $exibido);
        // Rascunhos e pendências: todos os anos (inclui o próximo exercício em preparação).
        $incompletos = $programas->filter(fn ($p) => $p->pendencias() !== [])->count();
        $rascunhos = $programas->where('status', StatusPrograma::Rascunho)->count();

        return [
            Stat::make("Publicados em {$exibido}", $publicados->count())
                ->description(Formato::moedaCurta($publicados->sum('valor')).' no site'),
            Stat::make('Rascunhos', $rascunhos)
                ->description('Ainda não publicados: confira e publique')
                ->color($rascunhos ? 'warning' : 'gray'),
            Stat::make('Fichas a completar', $incompletos)
                ->description('Faltam informações para o cidadão')
                ->color($incompletos ? 'danger' : 'success'),
        ];
    }
}
