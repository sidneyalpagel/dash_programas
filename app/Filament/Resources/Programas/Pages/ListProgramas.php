<?php

namespace App\Filament\Resources\Programas\Pages;

use App\Enums\StatusPrograma;
use App\Filament\Resources\Programas\ProgramaResource;
use App\Models\Configuracao;
use App\Models\Programa;
use App\Support\Panorama;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProgramas extends ListRecords
{
    protected static string $resource = ProgramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->exercicioNoSite(),
            CreateAction::make(),
        ];
    }

    /**
     * O site só muda de ano quando o administrador decide, depois que as
     * secretarias completarem o novo exercício.
     */
    private function exercicioNoSite(): Action
    {
        return Action::make('exercicioNoSite')
            ->label(fn () => 'No site: '.Panorama::exercicioExibido())
            ->icon(Heroicon::OutlinedGlobeAlt)
            ->color('gray')
            ->visible(fn () => auth()->user()->isAdmin())
            ->modalHeading('Exercício exibido no site')
            ->modalDescription('É o ano que o cidadão vê ao abrir o site. Os demais anos com programas publicados continuam acessíveis pelo seletor de exercício.')
            ->modalSubmitActionLabel('Salvar')
            ->fillForm(fn () => ['exercicio' => Panorama::exercicioExibido()])
            ->schema([
                Radio::make('exercicio')
                    ->label('Mostrar no site')
                    ->options(fn () => collect(Panorama::exerciciosDisponiveis())
                        ->mapWithKeys(fn (int $ano) => [$ano => (string) $ano])
                        ->all())
                    ->descriptions(fn () => collect(Panorama::exerciciosDisponiveis())
                        ->mapWithKeys(function (int $ano) {
                            $publicados = Programa::doExercicio($ano)->publicados()->count();
                            $pendentes = Programa::doExercicio($ano)->where('status', '!=', StatusPrograma::Publicado)->count();

                            return [$ano => "{$publicados} publicados".($pendentes ? " · {$pendentes} em rascunho ou revisão" : '')];
                        })
                        ->all())
                    ->helperText('Só aparecem anos com pelo menos um programa publicado.')
                    ->required(),
            ])
            ->action(function (array $data) {
                Configuracao::definir(Configuracao::EXERCICIO_EXIBICAO, (int) $data['exercicio']);

                Notification::make()->title('O site agora mostra o exercício '.$data['exercicio'].'.')->success()->send();
            });
    }
}
