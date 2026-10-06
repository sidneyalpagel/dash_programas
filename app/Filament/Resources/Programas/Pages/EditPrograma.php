<?php

namespace App\Filament\Resources\Programas\Pages;

use App\Enums\StatusPrograma;
use App\Filament\Actions\CopiarParaExercicio;
use App\Filament\Resources\Programas\ProgramaResource;
use App\Models\Programa;
use App\Support\DescricaoAlteracao;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Programa $record
 */
class EditPrograma extends EditRecord
{
    protected static string $resource = ProgramaResource::class;

    private bool $foiProposta = false;

    private function admin(): bool
    {
        return auth()->user()->isAdmin();
    }

    /** O servidor continua editando a versão que propôs, não a publicada. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! $this->admin() && $this->record->temAlteracaoPendente()) {
            return array_merge($data, $this->record->alteracoes_pendentes);
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Programa $record */
        if (! $this->admin() && $record->status === StatusPrograma::Publicado) {
            $record->proporAlteracoes($data);
            $this->foiProposta = true;

            return $record;
        }

        return parent::handleRecordUpdate($record, $data);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return $this->foiProposta
            ? 'Alterações enviadas para revisão. O site continua mostrando a versão publicada até a aprovação.'
            : 'Programa salvo.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('revisar')
                ->label('Revisar alterações')
                ->icon(Heroicon::OutlinedDocumentMagnifyingGlass)
                ->color('warning')
                ->visible(fn () => $this->admin() && $this->record->temAlteracaoPendente())
                ->modalHeading('Alterações propostas pela secretaria')
                ->modalDescription('Compare a versão publicada com a proposta. Ao aprovar, a nova versão entra no ar imediatamente.')
                ->modalContent(fn () => view('filament.revisao-alteracoes', [
                    'linhas' => DescricaoAlteracao::linhas($this->record->diferencasPropostas()),
                    'rotuloDe' => 'Publicado hoje',
                    'rotuloPara' => 'Proposta da secretaria',
                ]))
                ->modalWidth('4xl')
                ->modalSubmitActionLabel('Aprovar e publicar')
                ->action(function () {
                    $this->record->aprovarAlteracoes();
                    $this->refreshFormData(array_keys($this->record->getAttributes()));
                    Notification::make()->title('Alterações aprovadas e publicadas.')->success()->send();
                })
                ->extraModalFooterActions(fn (Action $action) => [
                    $action->makeModalSubmitAction('descartar', ['descartar' => true])
                        ->label('Descartar proposta')
                        ->color('danger'),
                ])
                ->before(function (array $arguments, Action $action) {
                    if ($arguments['descartar'] ?? false) {
                        $this->record->descartarAlteracoes();
                        Notification::make()->title('Proposta descartada.')->send();
                        $action->cancel();
                    }
                }),

            Action::make('enviarRevisao')
                ->label('Enviar para revisão')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->visible(fn () => ! $this->admin() && $this->record->status === StatusPrograma::Rascunho)
                ->requiresConfirmation()
                ->modalDescription('O administrador vai revisar a ficha antes de publicá-la no site. Você ainda poderá editar enquanto isso.')
                ->action(function () {
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    $this->record->mudarStatus(StatusPrograma::EmRevisao, 'enviado para revisão');
                    Notification::make()->title('Enviado para revisão.')->success()->send();
                }),

            Action::make('publicar')
                ->label('Publicar no site')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->color('success')
                ->visible(fn () => $this->admin() && $this->record->status !== StatusPrograma::Publicado)
                ->requiresConfirmation()
                ->modalDescription('A ficha ficará visível para qualquer cidadão no site.')
                ->action(function () {
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    $this->record->mudarStatus(StatusPrograma::Publicado, 'publicado');
                    Notification::make()->title('Programa publicado.')->success()->send();
                }),

            Action::make('verNoSite')
                ->label('Ver no site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->visible(fn () => $this->record->status === StatusPrograma::Publicado)
                ->url(fn () => $this->record->urlPublica(), shouldOpenInNewTab: true),

            ActionGroup::make([
                CopiarParaExercicio::individual(),
                Action::make('despublicar')
                    ->label('Tirar do site (voltar a rascunho)')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->visible(fn () => $this->admin() && $this->record->status === StatusPrograma::Publicado)
                    ->requiresConfirmation()
                    ->action(fn () => $this->record->mudarStatus(StatusPrograma::Rascunho, 'retirado do site')),
                Action::make('devolver')
                    ->label('Devolver para a secretaria')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->visible(fn () => $this->admin() && $this->record->status === StatusPrograma::EmRevisao)
                    ->requiresConfirmation()
                    ->action(fn () => $this->record->mudarStatus(StatusPrograma::Rascunho, 'devolvido para ajustes')),
                DeleteAction::make(),
            ]),
        ];
    }
}
