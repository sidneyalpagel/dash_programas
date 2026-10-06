<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Programas\ProgramaResource;
use App\Models\Programa;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * Copia programas para outro exercício como rascunho. Vai tudo o que descreve
 * o programa; valor e quantidades ficam em branco para a secretaria informar.
 */
class CopiarParaExercicio
{
    private const DESCRICAO = 'Será criado um rascunho com a descrição, como participar, leis, público e tipo. '
        .'Valor e quantidade de atendidos ficam em branco para a secretaria informar os números do novo ano.';

    /** Copiar vários programas de uma vez (ex.: todos os de 2025 para 2026). */
    public static function emLote(): BulkAction
    {
        return BulkAction::make('copiarParaExercicio')
            ->label('Copiar para outro exercício')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->modalHeading('Copiar programas para outro exercício')
            ->modalDescription(self::DESCRICAO)
            ->modalSubmitActionLabel('Copiar')
            ->schema(fn (Collection $records) => [self::campoAno((int) $records->max('exercicio') + 1)])
            ->action(function (Collection $records, array $data) {
                $ano = (int) $data['exercicio'];
                $copiados = $records->map(fn (Programa $p) => $p->copiarPara($ano))->filter()->count();
                $ignorados = $records->count() - $copiados;

                Notification::make()
                    ->title("{$copiados} ".($copiados === 1 ? 'programa copiado' : 'programas copiados')." para {$ano} como rascunho.")
                    ->body($ignorados
                        ? "{$ignorados} ".($ignorados === 1 ? 'já existia' : 'já existiam')." em {$ano} (ou já eram de {$ano}) e não ".($ignorados === 1 ? 'foi copiado' : 'foram copiados').'.'
                        : 'Filtre a tabela pelo ano para completar valores e quantidades.')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /** Copiar só este programa (página de edição). */
    public static function individual(): Action
    {
        return Action::make('copiarParaExercicio')
            ->label('Copiar para outro exercício')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->modalHeading('Copiar programa para outro exercício')
            ->modalDescription(self::DESCRICAO)
            ->modalSubmitActionLabel('Copiar')
            ->schema(fn (Programa $record) => [self::campoAno($record->exercicio + 1)])
            ->action(function (Programa $record, array $data, Action $action) {
                $ano = (int) $data['exercicio'];
                $copia = $record->copiarPara($ano);

                if (! $copia) {
                    Notification::make()
                        ->title("Este programa já existe em {$ano}.")
                        ->warning()
                        ->send();

                    $action->halt();
                }

                Notification::make()->title("Rascunho de {$ano} criado. Complete o valor e a quantidade de atendidos.")->success()->send();
                $action->redirect(ProgramaResource::getUrl('edit', ['record' => $copia]));
            });
    }

    private static function campoAno(int $padrao): TextInput
    {
        return TextInput::make('exercicio')
            ->label('Copiar para o exercício')
            ->integer()
            ->minValue(2000)
            ->maxValue(2100)
            ->default($padrao)
            ->required();
    }
}
