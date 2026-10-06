<?php

namespace App\Http\Controllers;

use App\Enums\Mecanismo;
use App\Enums\PublicoAlvo;
use App\Models\Programa;
use App\Models\Secretaria;
use App\Support\Panorama;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteController extends Controller
{
    /**
     * Exercício da página: o do endereço (/2024/...) ou o exibido no site.
     * Ano sem nada publicado dá 404; o próprio ano exibido redireciona para o endereço sem ano.
     */
    private function panorama(Request $request): Panorama
    {
        $ano = $request->route('ano');

        if ($ano === null) {
            return new Panorama(Panorama::exercicioExibido());
        }

        $ano = (int) $ano;
        abort_unless(in_array($ano, Panorama::exerciciosDisponiveis(), true), 404);

        if ($ano === Panorama::exercicioExibido()) {
            $nome = Str::after($request->route()->getName(), 'ano.');
            $parametros = array_diff_key($request->route()->parameters(), ['ano' => true]);

            throw new HttpResponseException(redirect()->to(
                route($nome, $parametros).($request->getQueryString() ? '?'.$request->getQueryString() : ''),
                301,
            ));
        }

        return new Panorama($ano);
    }

    public function inicio(Request $request): View
    {
        return view('site.inicio', [
            'panorama' => $this->panorama($request),
        ]);
    }

    public function programas(Request $request): View
    {
        $panorama = $this->panorama($request);

        $filtros = [
            'perfil' => PublicoAlvo::tryFrom((string) $request->query('perfil')),
            'secretaria' => Secretaria::where('slug', (string) $request->query('secretaria'))->first(),
            'tipo' => Mecanismo::tryFrom((string) $request->query('tipo')),
            'busca' => Str::limit(trim((string) $request->query('busca')), 80, ''),
            // Só aceita grupos que existem (ex.: "Renda Santa Helena", "Fomento Paraná").
            'grupo' => $panorama->programas->pluck('grupo')->filter()->unique()
                ->first(fn (string $grupo) => $grupo === trim((string) $request->query('grupo'))),
        ];

        $ordem = in_array($request->query('ordem'), ['valor', 'nome'], true) ? $request->query('ordem') : 'nome';

        $programas = $panorama->programas
            ->when($filtros['perfil'], fn ($c, $perfil) => $c->filter(fn (Programa $p) => in_array($perfil->value, $p->publico_alvo ?? [], true)))
            ->when($filtros['grupo'], fn ($c, $grupo) => $c->where('grupo', $grupo))
            ->when($filtros['secretaria'], fn ($c, $secretaria) => $c->where('secretaria_id', $secretaria->id))
            ->when($filtros['tipo'], fn ($c, $tipo) => $c->where('mecanismo', $tipo))
            ->when($filtros['busca'], fn ($c, $busca) => $c->filter(fn (Programa $p) => Str::contains(
                Str::ascii(Str::lower($p->nome.' '.$p->grupo.' '.$p->descricao)),
                Str::ascii(Str::lower($busca)),
            )))
            ->sortBy(fn (Programa $p) => $ordem === 'valor' ? -1 * (float) $p->valor : Str::ascii($p->nome))
            ->values();

        return view('site.programas', [
            'panorama' => $panorama,
            'programas' => $programas,
            'filtros' => $filtros,
            'ordem' => $ordem,
            'secretarias' => Secretaria::all(),
            'filtrado' => collect($filtros)->filter()->isNotEmpty(),
        ]);
    }

    public function programa(Request $request): View
    {
        $panorama = $this->panorama($request);

        $programa = Programa::publicados()
            ->doExercicio($panorama->exercicio)
            ->where('slug', (string) $request->route('slug'))
            ->with('secretaria')
            ->firstOrFail();

        return $this->ficha($programa, $panorama);
    }

    /** A ficha como ficará no site, para quem edita o programa conferir antes de publicar. */
    public function previa(Request $request, Programa $programa): View
    {
        abort_unless($request->user()->can('update', $programa), 403);

        return $this->ficha($programa->load('secretaria'), new Panorama($programa->exercicio), previa: true);
    }

    private function ficha(Programa $programa, Panorama $panorama, bool $previa = false): View
    {
        return view('site.programa', [
            'programa' => $programa,
            'panorama' => $panorama,
            'previa' => $previa,
            'relacionados' => Programa::publicados()
                ->doExercicio($programa->exercicio)
                ->whereKeyNot($programa->id)
                ->where(fn ($q) => filled($programa->grupo)
                    ? $q->where('grupo', $programa->grupo)
                    : $q->where('secretaria_id', $programa->secretaria_id))
                ->orderByDesc('valor')
                ->limit(4)
                ->get(),
        ]);
    }

    public function entenda(Request $request): View
    {
        return view('site.entenda', [
            'panorama' => $this->panorama($request),
        ]);
    }
}
