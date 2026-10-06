<?php

namespace App\Support;

use App\Enums\Mecanismo;
use App\Enums\PublicoAlvo;
use App\Models\Configuracao;
use App\Models\Programa;
use App\Models\Secretaria;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Números agregados de um exercício, calculados só com programas publicados.
 */
class Panorama
{
    /** @var Collection<int, Programa> */
    public readonly Collection $programas;

    public function __construct(public readonly int $exercicio)
    {
        $this->programas = Programa::publicados()
            ->doExercicio($exercicio)
            ->with('secretaria')
            ->orderBy('nome')
            ->get();
    }

    /**
     * Exercício que o site mostra por padrão. É escolhido pelo administrador no
     * painel; enquanto não houver escolha (ou se o ano escolhido não tiver nada
     * publicado), usa o mais recente com programas publicados.
     */
    public static function exercicioExibido(): int
    {
        $disponiveis = static::exerciciosDisponiveis();
        $escolhido = (int) Configuracao::obter(Configuracao::EXERCICIO_EXIBICAO);

        if (in_array($escolhido, $disponiveis, true)) {
            return $escolhido;
        }

        return $disponiveis[0] ?? (int) now()->year;
    }

    /** @deprecated Use exercicioExibido(). */
    public static function exercicioAtual(): int
    {
        return static::exercicioExibido();
    }

    /** Exercícios com algo publicado, do mais recente ao mais antigo. @return list<int> */
    public static function exerciciosDisponiveis(): array
    {
        return array_map('intval', Programa::publicados()->distinct()->orderByDesc('exercicio')->pluck('exercicio')->all());
    }

    public function ehExibido(): bool
    {
        return $this->exercicio === static::exercicioExibido();
    }

    /**
     * Rota do site mantendo o ano: sem prefixo para o exercício exibido,
     * com /{ano}/ para os demais.
     */
    public function rota(string $nome, array $parametros = []): string
    {
        return $this->ehExibido()
            ? route($nome, $parametros)
            : route('ano.'.$nome, ['ano' => $this->exercicio] + $parametros);
    }

    public function total(): float
    {
        return (float) $this->programas->sum('valor');
    }

    public function quantidade(): int
    {
        return $this->programas->count();
    }

    public function semCusto(): int
    {
        return $this->programas->filter->semCustoDireto()->count();
    }

    /** @return Collection<int, array{secretaria: Secretaria, total: float, quantidade: int, fracao: float}> */
    public function porSecretaria(): Collection
    {
        $total = $this->total() ?: 1;

        return $this->programas
            ->groupBy('secretaria_id')
            ->map(fn (Collection $grupo) => [
                'secretaria' => $grupo->first()->secretaria,
                'total' => (float) $grupo->sum('valor'),
                'quantidade' => $grupo->count(),
                'fracao' => $grupo->sum('valor') / $total,
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Ordem fixa dos tipos (a cor de cada um segue a ordem, não o tamanho).
     *
     * @return Collection<int, array{mecanismo: Mecanismo, total: float, quantidade: int, fracao: float, exemplos: Collection}>
     */
    public function porMecanismo(): Collection
    {
        $total = $this->total() ?: 1;

        return collect(Mecanismo::cases())->map(function (Mecanismo $mecanismo) use ($total) {
            $grupo = $this->programas->where('mecanismo', $mecanismo);

            return [
                'mecanismo' => $mecanismo,
                'total' => (float) $grupo->sum('valor'),
                'quantidade' => $grupo->count(),
                'fracao' => $grupo->sum('valor') / $total,
                'exemplos' => $grupo->sortByDesc('valor')->take(3)->pluck('nome'),
            ];
        });
    }

    /** Parte que chega como dinheiro ou incentivo direto (todos menos serviços). */
    public function fracaoDireta(): float
    {
        $total = $this->total() ?: 1;

        return $this->programas->where('mecanismo', '!=', Mecanismo::ServicoPublico)->sum('valor') / $total;
    }

    /** @return Collection<int, Programa> */
    public function maiores(int $quantos = 10): Collection
    {
        return $this->comCusto()->take($quantos);
    }

    /** Programas com custo direto, do maior para o menor valor. */
    public function comCusto(): Collection
    {
        return $this->programas->filter->temCustoDireto()->sortByDesc('valor')->values();
    }

    /** Posição do programa entre os que têm custo direto (1 = maior), ou null. */
    public function posicao(Programa $programa): ?int
    {
        $indice = $this->comCusto()->search(fn (Programa $p) => $p->is($programa));

        return $indice === false ? null : $indice + 1;
    }

    /**
     * Programas com mais atendidos para a faixa "Quem é atendido": o maior de
     * cada secretaria primeiro (para mostrar a variedade) e depois os maiores no geral.
     *
     * @return Collection<int, Programa>
     */
    public function destaquesAtendidos(int $quantos = 6): Collection
    {
        $comAtendidos = $this->programas
            ->filter(fn (Programa $p) => $p->qtd_atendidos > 0
                && filled($p->unidade_atendidos)
                && mb_strtolower($p->unidade_atendidos) !== 'atendidos')
            ->sortByDesc('qtd_atendidos');

        $umPorSecretaria = $comAtendidos->unique('secretaria_id');

        return $umPorSecretaria
            ->concat($comAtendidos->diff($umPorSecretaria))
            ->take($quantos)
            ->sortByDesc('qtd_atendidos')
            ->values();
    }

    /** @return Collection<int, array{perfil: PublicoAlvo, quantidade: int}> */
    public function perfis(): Collection
    {
        return collect(PublicoAlvo::cases())
            ->map(fn (PublicoAlvo $perfil) => [
                'perfil' => $perfil,
                'quantidade' => $this->programas->filter(fn (Programa $p) => in_array($perfil->value, $p->publico_alvo ?? [], true))->count(),
            ])
            ->filter(fn (array $item) => $item['quantidade'] > 0)
            ->values();
    }

    /** Programas agrupados pelo ano de criação, do mais antigo ao mais recente. */
    public function linhaDoTempo(): Collection
    {
        return $this->programas
            ->whereNotNull('ano_criacao')
            ->sortBy('ano_criacao')
            ->groupBy('ano_criacao');
    }

    public function ultimaAtualizacao(): ?CarbonInterface
    {
        return $this->programas->max('updated_at');
    }
}
