<?php

namespace App\Filament\Widgets;

use App\Enums\StatusPrograma;
use App\Filament\Resources\Programas\ProgramaResource;
use App\Support\Formato;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumoProgramas extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $programas = ProgramaResource::getEloquentQuery()->get();
        $publicados = $programas->where('status', StatusPrograma::Publicado);
        $incompletos = $programas->filter(fn ($p) => $p->pendencias() !== [])->count();
        $aguardando = $programas->filter(fn ($p) => $p->status === StatusPrograma::EmRevisao || $p->temAlteracaoPendente())->count();

        return [
            Stat::make('Programas publicados', $publicados->count())
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
