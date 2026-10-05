<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Programas\ProgramaResource;
use App\Models\Programa;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class FichasIncompletas extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Fichas a completar';

    public function table(Table $table): Table
    {
        return $table
            ->description('Programas com informações faltando. Complete para que o cidadão entenda o programa.')
            ->query(fn () => ProgramaResource::getEloquentQuery()->where(fn (Builder $q) => $q
                ->whereNull('descricao')
                ->orWhereNull('como_participar')
                ->orWhereNull('bases_legais')
                ->orWhereNull('publico_alvo')
                ->orWhere('fonte_recurso', 'nao_informado')
                ->orWhere(fn (Builder $q) => $q->whereNull('qtd_atendidos')->whereNull('qtd_beneficios'))))
            ->defaultSort('nome')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('nome')->label('Programa')->wrap()->searchable(),
                TextColumn::make('secretaria.nome_curto')
                    ->label('Secretaria')
                    ->visible(fn () => auth()->user()->isAdmin()),
                TextColumn::make('faltando')
                    ->label('O que falta')
                    ->state(fn (Programa $record) => $record->pendencias())
                    ->badge()
                    ->color('warning'),
            ])
            ->recordActions([
                Action::make('completar')
                    ->label('Completar')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Programa $record) => ProgramaResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
