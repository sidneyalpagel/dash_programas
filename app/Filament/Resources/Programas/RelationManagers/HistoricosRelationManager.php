<?php

namespace App\Filament\Resources\Programas\RelationManagers;

use App\Models\ProgramaHistorico;
use App\Support\DescricaoAlteracao;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HistoricosRelationManager extends RelationManager
{
    protected static string $relationship = 'historicos';

    protected static ?string $title = 'Histórico de alterações';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->paginated([10, 25])
            ->columns([
                TextColumn::make('created_at')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i'),
                TextColumn::make('user.name')
                    ->label('Quem')
                    ->placeholder('Sistema'),
                TextColumn::make('acao')
                    ->label('O quê')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('alteracoes')
                    ->label('Campos')
                    ->state(fn (ProgramaHistorico $record) => collect(array_keys($record->alteracoes ?? []))
                        ->map(fn ($campo) => DescricaoAlteracao::ROTULOS[$campo] ?? $campo)
                        ->join(', '))
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('detalhes')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (ProgramaHistorico $record) => filled($record->alteracoes))
                    ->modalHeading(fn (ProgramaHistorico $record) => ucfirst($record->acao).' em '.$record->created_at->format('d/m/Y H:i'))
                    ->modalContent(fn (ProgramaHistorico $record) => view('filament.revisao-alteracoes', [
                        'linhas' => DescricaoAlteracao::linhas($record->alteracoes),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),
            ]);
    }
}
