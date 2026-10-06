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
        // Pendências e revisões: todos os anos (inclui rascunhos do próximo exercício).
        $incompletos = $programas->filter(fn ($p) => $p->pendencias() !== [])->count();
        $aguardando = $programas->filter(fn ($p) => $p->status === StatusPrograma::EmRevisao || $p->temAlteracaoPendente())->count();

        return [
            Stat::make("Publicados em {$exibido}", $publicados->count())
                ->description(Formato::moedaCurta($publicados->sum('valor')).' no site'),
            Stat::make('Aguardando revisão', $aguardando)
                ->description(auth()->user()->isAdmin() ? 'Revise e publique' : 'Com o administrador')
                ->color($aguardando ? 'warning' : 'gray'),
            Stat::make('Fichas a completar', $incompletos)
                ->description('Faltam informações para o cidadão')
                ->color($incompletos ? 'danger' : 'success'),
        ];
    }
}
