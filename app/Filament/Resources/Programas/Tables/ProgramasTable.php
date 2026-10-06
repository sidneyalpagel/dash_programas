<?php

namespace App\Filament\Resources\Programas\Tables;

use App\Enums\Mecanismo;
use App\Enums\StatusPrograma;
use App\Filament\Actions\CopiarParaExercicio;
use App\Models\Programa;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgramasTable
{
    public static function configure(Table $table): Table
    {
        $admin = fn () => auth()->user()->isAdmin();

        return $table
            ->defaultSort('nome')
            ->columns([
                TextColumn::make('nome')
                    ->label('Programa')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (Programa $record) => $record->grupo),
                TextColumn::make('secretaria.nome_curto')
                    ->label('Secretaria')
                    ->sortable()
                    ->visible($admin),
                TextColumn::make('mecanismo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (Mecanismo $state) => $state->rotuloCurto())
                    ->toggleable(),
                TextColumn::make('valor')
                    ->label('Valor no ano')
                    ->money('BRL', locale: 'pt_BR')
                    ->placeholder(fn (Programa $record) => $record->semCustoDireto() ? 'Sem custo direto' : 'A informar')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('pendencias')
                    ->label('Ficha')
                    ->state(fn (Programa $record) => count($record->pendencias()))
                    ->formatStateUsing(fn (int $state) => $state === 0 ? 'Completa' : $state.' a completar')
                    ->badge()
                    ->color(fn (int $state) => $state === 0 ? 'success' : 'warning')
                    ->tooltip(fn (Programa $record) => implode(', ', $record->pendencias()) ?: null),
                TextColumn::make('status')
                    ->label('Situação')
                    ->badge()
                    ->sortable(),
                TextColumn::make('exercicio')
                    ->label('Ano')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('secretaria')
                    ->relationship('secretaria', 'nome_curto')
                    ->visible($admin),
                SelectFilter::make('status')
                    ->label('Situação')
                    ->options(StatusPrograma::class),
                SelectFilter::make('mecanismo')
                    ->label('Tipo')
                    ->options(Mecanismo::class),
                SelectFilter::make('exercicio')
                    ->label('Ano')
                    ->options(fn () => Programa::query()->distinct()->orderByDesc('exercicio')->pluck('exercicio', 'exercicio')->all()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                // Virada de ano: selecione os programas e copie como rascunho para o novo exercício.
                CopiarParaExercicio::emLote(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible($admin),
            ]);
    }
}
