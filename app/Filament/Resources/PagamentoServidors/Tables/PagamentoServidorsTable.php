<?php

namespace App\Filament\Resources\PagamentoServidors\Tables;

use App\Models\PagamentoServidor;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PagamentoServidorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(fn ($query) => $query->orderByDesc('exercicio')->orderBy('ordem'))
            ->columns([
                TextColumn::make('exercicio')->label('Ano')->sortable(),
                TextColumn::make('verba')->label('Verba')->searchable()->wrap(),
                TextColumn::make('qtd_servidores')->label('Servidores')->numeric(locale: 'pt_BR')->alignEnd(),
                TextColumn::make('valor')->label('Valor')->money('BRL', locale: 'pt_BR')->alignEnd()->sortable(),
                TextColumn::make('situacao')
                    ->label('No site')
                    ->state(fn (PagamentoServidor $record) => $record->publicado ? 'Publicado' : 'Oculto')
                    ->badge()
                    ->color(fn (string $state) => $state === 'Publicado' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('exercicio')
                    ->label('Ano')
                    ->options(fn () => PagamentoServidor::query()->distinct()->orderByDesc('exercicio')->pluck('exercicio', 'exercicio')->all()),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
