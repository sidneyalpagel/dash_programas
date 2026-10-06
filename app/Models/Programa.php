<?php

namespace App\Models;

use App\Enums\FonteRecurso;
use App\Enums\Mecanismo;
use App\Enums\PublicoAlvo;
use App\Enums\StatusPrograma;
use App\Enums\TipoValor;
use App\Support\Panorama;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

#[Fillable([
    'secretaria_id', 'exercicio', 'nome', 'slug', 'grupo',
    'descricao', 'como_participar', 'bases_legais', 'ano_criacao',
    'mecanismo', 'publico_alvo', 'fonte_recurso',
    'qtd_atendidos', 'unidade_atendidos', 'qtd_beneficios', 'detalhe_atendidos',
    'tipo_valor', 'valor', 'valor_total_vigencia', 'vigencia_anos',
    'nota_publica', 'observacao_interna',
    'status', 'alteracoes_pendentes', 'atualizado_por', 'publicado_em',
])]
class Programa extends Model
{
    /**
     * Campos de conteúdo que um servidor de secretaria pode propor alterar.
     * Status, slug e auditoria ficam de fora.
     */
    public const CAMPOS_CONTEUDO = [
        'exercicio', 'nome', 'grupo',
        'descricao', 'como_participar', 'bases_legais', 'ano_criacao',
        'mecanismo', 'publico_alvo', 'fonte_recurso',
        'qtd_atendidos', 'unidade_atendidos', 'qtd_beneficios', 'detalhe_atendidos',
        'tipo_valor', 'valor', 'valor_total_vigencia', 'vigencia_anos',
        'nota_publica', 'observacao_interna',
    ];

    /** Ação registrada no histórico no próximo save (padrão: "editado"). */
    public ?string $acaoHistorico = null;

    protected function casts(): array
    {
        return [
            'exercicio' => 'integer',
            'ano_criacao' => 'integer',
            'bases_legais' => 'array',
            'publico_alvo' => 'array',
            'mecanismo' => Mecanismo::class,
            'fonte_recurso' => FonteRecurso::class,
            'tipo_valor' => TipoValor::class,
            'status' => StatusPrograma::class,
            'qtd_atendidos' => 'integer',
            'qtd_beneficios' => 'integer',
            'valor' => 'decimal:2',
            'valor_total_vigencia' => 'decimal:2',
            'vigencia_anos' => 'integer',
            'alteracoes_pendentes' => 'array',
            'publicado_em' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Programa $programa) {
            if (blank($programa->slug)) {
                $programa->slug = static::slugUnico($programa->nome, (int) $programa->exercicio);
            }

            // Listas vazias viram null para que "falta preencher" seja só whereNull.
            foreach (['bases_legais', 'publico_alvo'] as $campo) {
                if ($programa->{$campo} === []) {
                    $programa->{$campo} = null;
                }
            }

            $programa->calcularValor();

            if (Auth::check()) {
                $programa->atualizado_por = Auth::id();
            }
        });

        static::created(function (Programa $programa) {
            $programa->registrarHistorico($programa->acaoHistorico ?? 'criado');
            $programa->acaoHistorico = null;
        });

        static::updated(function (Programa $programa) {
            $ignorar = ['updated_at', 'atualizado_por', 'alteracoes_pendentes'];
            $mudancas = [];

            foreach (array_diff(array_keys($programa->getChanges()), $ignorar) as $campo) {
                $mudancas[$campo] = [
                    'de' => $programa->getRawOriginal($campo),
                    'para' => $programa->getAttributes()[$campo] ?? null,
                ];
            }

            if ($mudancas !== [] || $programa->acaoHistorico !== null) {
                $programa->registrarHistorico($programa->acaoHistorico ?? 'editado', $mudancas ?: null);
            }

            $programa->acaoHistorico = null;
        });
    }

    /** Endereço único dentro do exercício: o mesmo programa mantém o slug de um ano para o outro. */
    public static function slugUnico(string $nome, int $exercicio): string
    {
        $base = Str::slug(Str::limit($nome, 80, ''));
        $slug = $base;
        $i = 2;

        while (static::where('exercicio', $exercicio)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /** Ficha pública: sem o ano no endereço quando é o exercício exibido no site. */
    public function urlPublica(): string
    {
        return $this->exercicio === Panorama::exercicioExibido()
            ? route('programas.show', ['slug' => $this->slug])
            : route('ano.programas.show', ['ano' => $this->exercicio, 'slug' => $this->slug]);
    }

    /**
     * Cria um rascunho do programa em outro exercício. Copia o que descreve o
     * programa; valores e quantidades ficam em branco para a secretaria informar.
     * Retorna null se o programa já existe no exercício de destino.
     */
    public function copiarPara(int $exercicio): ?self
    {
        $existe = static::where('secretaria_id', $this->secretaria_id)
            ->where('exercicio', $exercicio)
            ->where(fn (Builder $q) => $q->where('nome', $this->nome)->orWhere('slug', $this->slug))
            ->exists();

        if ($existe || $exercicio === $this->exercicio) {
            return null;
        }

        $copia = $this->replicate([
            'valor', 'valor_total_vigencia', 'qtd_atendidos', 'qtd_beneficios', 'detalhe_atendidos',
            'observacao_interna', 'alteracoes_pendentes', 'publicado_em', 'atualizado_por',
        ]);
        $copia->exercicio = $exercicio;
        $copia->status = StatusPrograma::Rascunho;
        $copia->acaoHistorico = "copiado de {$this->exercicio}";
        $copia->save();

        return $copia;
    }

    /** Programas plurianuais têm o valor anual calculado a partir do total. */
    public function calcularValor(): void
    {
        if ($this->tipo_valor === TipoValor::SemCusto) {
            $this->valor = null;
            $this->valor_total_vigencia = null;
            $this->vigencia_anos = null;

            return;
        }

        if ($this->tipo_valor === TipoValor::Anualizado) {
            if ($this->valor_total_vigencia && $this->vigencia_anos) {
                $this->valor = round((float) $this->valor_total_vigencia / $this->vigencia_anos, 2);
            }

            return;
        }

        $this->valor_total_vigencia = null;
        $this->vigencia_anos = null;
    }

    public function registrarHistorico(string $acao, ?array $alteracoes = null): void
    {
        $this->historicos()->create([
            'user_id' => Auth::id(),
            'acao' => $acao,
            'alteracoes' => $alteracoes,
        ]);
    }

    // Relações -----------------------------------------------------------

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(ProgramaHistorico::class)->latest('id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atualizado_por');
    }

    // Escopos ------------------------------------------------------------

    public function scopePublicados(Builder $query): Builder
    {
        return $query->where('status', StatusPrograma::Publicado);
    }

    public function scopeDoExercicio(Builder $query, int $exercicio): Builder
    {
        return $query->where('exercicio', $exercicio);
    }

    public function scopeComPerfil(Builder $query, PublicoAlvo $perfil): Builder
    {
        return $query->whereJsonContains('publico_alvo', $perfil->value);
    }

    // Fluxo de publicação -----------------------------------------------

    /**
     * Guarda as alterações de um programa já publicado sem mexer no que o
     * cidadão vê. Elas só entram no ar quando o administrador aprova.
     */
    public function proporAlteracoes(array $dados): void
    {
        $proposta = [];

        foreach (self::CAMPOS_CONTEUDO as $campo) {
            if (array_key_exists($campo, $dados)) {
                $valor = $dados[$campo];
                $proposta[$campo] = $valor instanceof \BackedEnum ? $valor->value : $valor;
            }
        }

        $this->alteracoes_pendentes = $proposta;
        $this->saveQuietly();
        $this->registrarHistorico('alteração proposta', $this->diferencasPropostas());
    }

    /** Campos em que a proposta difere da versão publicada: [campo => [de, para]]. */
    public function diferencasPropostas(): array
    {
        if (! $this->temAlteracaoPendente()) {
            return [];
        }

        $diferencas = [];

        foreach ($this->alteracoes_pendentes as $campo => $novo) {
            $atual = $this->getAttribute($campo);
            $atual = $atual instanceof \BackedEnum ? $atual->value : $atual;

            if (in_array($campo, ['valor', 'valor_total_vigencia'], true)) {
                $iguais = round((float) $atual, 2) === round((float) $novo, 2) && ($atual === null) === ($novo === null);
            } else {
                $iguais = json_encode($atual) === json_encode($novo) || ((string) $atual === (string) $novo && ! is_array($novo));
            }

            if (! $iguais) {
                $diferencas[$campo] = ['de' => $atual, 'para' => $novo];
            }
        }

        return $diferencas;
    }

    public function aprovarAlteracoes(): void
    {
        $this->fill($this->alteracoes_pendentes ?? []);
        $this->alteracoes_pendentes = null;
        $this->publicado_em = now();
        $this->acaoHistorico = 'alteração aprovada';
        $this->save();
    }

    public function descartarAlteracoes(): void
    {
        $this->alteracoes_pendentes = null;
        $this->saveQuietly();
        $this->registrarHistorico('alteração descartada');
    }

    public function mudarStatus(StatusPrograma $status, string $acao): void
    {
        $this->status = $status;

        if ($status === StatusPrograma::Publicado) {
            $this->publicado_em = now();
        }

        $this->acaoHistorico = $acao;
        $this->save();
    }

    // Regras de leitura --------------------------------------------------

    public function temAlteracaoPendente(): bool
    {
        return filled($this->alteracoes_pendentes);
    }

    public function temCustoDireto(): bool
    {
        return $this->tipo_valor !== TipoValor::SemCusto && $this->valor !== null;
    }

    /** Programa classificado como sem gasto direto do Município. */
    public function semCustoDireto(): bool
    {
        return $this->tipo_valor === TipoValor::SemCusto;
    }

    /** Programa que tem custo, mas cujo valor a secretaria ainda não informou. */
    public function valorPendente(): bool
    {
        return ! $this->semCustoDireto() && $this->valor === null;
    }

    /** Frase da ficha para programas sem custo direto, conforme a origem do recurso. */
    public function explicacaoSemCusto(): string
    {
        return match ($this->fonte_recurso) {
            FonteRecurso::Estadual => 'O recurso é do Governo do Estado. Não há gasto direto do Município.',
            FonteRecurso::Federal => 'O recurso é da União. Não há gasto direto do Município.',
            default => 'Não há gasto direto do Município com este programa.',
        };
    }

    /** @return list<PublicoAlvo> */
    public function perfis(): array
    {
        return array_values(array_filter(array_map(
            fn (string $valor) => PublicoAlvo::tryFrom($valor),
            $this->publico_alvo ?? [],
        )));
    }

    /** Valor médio por atendido, só quando há custo e quantidade informada. */
    public function valorPorAtendido(): ?float
    {
        $quantidade = $this->qtd_atendidos ?: $this->qtd_beneficios;

        if (! $this->temCustoDireto() || ! $quantidade) {
            return null;
        }

        return (float) $this->valor / $quantidade;
    }

    /** Lista do que falta preencher para a ficha ficar completa para o cidadão. */
    public function pendencias(): array
    {
        $faltando = [];

        if (blank($this->descricao)) {
            $faltando[] = 'Descrição do programa';
        }

        if (blank($this->como_participar)) {
            $faltando[] = 'Como participar';
        }

        if (blank($this->bases_legais)) {
            $faltando[] = 'Base legal';
        }

        if (blank($this->publico_alvo)) {
            $faltando[] = 'Público-alvo';
        }

        if ($this->qtd_atendidos === null && $this->qtd_beneficios === null) {
            $faltando[] = 'Quantidade atendida';
        }

        if ($this->tipo_valor !== TipoValor::SemCusto && $this->valor === null) {
            $faltando[] = 'Valor';
        }

        if ($this->fonte_recurso === FonteRecurso::NaoInformado) {
            $faltando[] = 'Fonte do recurso';
        }

        return $faltando;
    }

    /** "2.434 estudantes", "1.139 benefícios pagos" ou "9 adolescentes · 47 benefícios". */
    public function atendidosTexto(): ?string
    {
        $partes = [];

        if ($this->qtd_atendidos !== null) {
            $partes[] = number_format($this->qtd_atendidos, 0, ',', '.').' '.($this->unidade_atendidos ?: 'atendidos');
        }

        if ($this->qtd_beneficios !== null) {
            $partes[] = number_format($this->qtd_beneficios, 0, ',', '.').' '.($this->qtd_beneficios === 1 ? 'benefício pago' : 'benefícios pagos');
        }

        return $partes ? implode(' · ', $partes) : null;
    }

    /** Leis em texto curto: "Lei nº 3.339/2025 e Lei nº 3.354/2025". */
    public function basesLegaisTexto(): ?string
    {
        if (blank($this->bases_legais)) {
            return null;
        }

        return collect($this->bases_legais)
            ->map(fn (array $lei) => trim(($lei['tipo'] ?? 'Lei').' '.(filled($lei['numero'] ?? null) ? 'nº '.$lei['numero'] : '').(filled($lei['ano'] ?? null) ? '/'.$lei['ano'] : '')))
            ->join(', ', ' e ');
    }
}
