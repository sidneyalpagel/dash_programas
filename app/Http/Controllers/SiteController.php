<?php

namespace App\Http\Controllers;

use App\Enums\Mecanismo;
use App\Enums\PublicoAlvo;
use App\Enums\StatusPrograma;
use App\Models\Programa;
use App\Models\Secretaria;
use App\Support\Panorama;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function inicio(): View
    {
        return view('site.inicio', [
            'panorama' => new Panorama(Panorama::exercicioAtual()),
        ]);
    }

    public function programas(Request $request): View
    {
        $panorama = new Panorama(Panorama::exercicioAtual());

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

    public function programa(Programa $programa): View
    {
        abort_unless($programa->status === StatusPrograma::Publicado, 404);

        $programa->load('secretaria');

        return view('site.programa', [
            'programa' => $programa,
            'panorama' => new Panorama($programa->exercicio),
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

    public function entenda(): View
    {
        return view('site.entenda', [
            'panorama' => new Panorama(Panorama::exercicioAtual()),
        ]);
    }
}
