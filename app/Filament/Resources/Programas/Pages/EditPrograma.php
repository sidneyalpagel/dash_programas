<?php

namespace App\Filament\Resources\Programas\Pages;

use App\Enums\StatusPrograma;
use App\Filament\Actions\CopiarParaExercicio;
use App\Filament\Resources\Programas\ProgramaResource;
use App\Models\Programa;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

/**
 * Quem cadastra também publica: revisa o rascunho com "Pré-visualizar" e
 * clica em "Publicar no site". Em programa publicado, salvar atualiza o site.
 *
 * @property Programa $record
 */
class EditPrograma extends EditRecord
{
    protected static string $resource = ProgramaResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return $this->record->estaPublicado()
            ? 'Alterações salvas e já publicadas no site.'
            : 'Rascunho salvo. Use "Pré-visualizar" para conferir e "Publicar no site" quando estiver pronto.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('previa')
                ->label('Pré-visualizar')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->visible(fn () => ! $this->record->estaPublicado())
                ->url(fn () => route('previa.programa', $this->record), shouldOpenInNewTab: true),

            Action::make('publicar')
                ->label('Publicar no site')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->color('success')
                ->visible(fn () => ! $this->record->estaPublicado())
                ->requiresConfirmation()
                ->modalIcon(Heroicon::OutlinedGlobeAlt)
                ->modalHeading('Publicar no site?')
                ->modalDescription(function () {
                    $faltando = $this->record->pendencias();

                    return 'A ficha ficará visível para qualquer cidadão. Confira antes com "Pré-visualizar".'
                        .($faltando ? ' Ainda falta: '.implode(', ', $faltando).'.' : '');
                })
                ->modalSubmitActionLabel('Publicar')
                ->action(function () {
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    $this->record->mudarStatus(StatusPrograma::Publicado, 'publicado');
                    Notification::make()->title('Programa publicado no site.')->success()->send();
                }),

            Action::make('verNoSite')
                ->label('Ver no site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->visible(fn () => $this->record->estaPublicado())
                ->url(fn () => $this->record->urlPublica(), shouldOpenInNewTab: true),

            ActionGroup::make([
                CopiarParaExercicio::individual(),
                Action::make('despublicar')
                    ->label('Tirar do site (voltar a rascunho)')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->visible(fn () => $this->record->estaPublicado())
                    ->requiresConfirmation()
                    ->modalDescription('A ficha deixa de aparecer no site e volta a ser rascunho. Você pode publicá-la de novo depois.')
                    ->action(function () {
                        $this->record->mudarStatus(StatusPrograma::Rascunho, 'retirado do site');
                        Notification::make()->title('Programa retirado do site.')->send();
                    }),
                DeleteAction::make(),
            ]),
        ];
    }
}
