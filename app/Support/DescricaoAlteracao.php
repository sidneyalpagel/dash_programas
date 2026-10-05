<?php

namespace App\Support;

use App\Enums\FonteRecurso;
use App\Enums\Mecanismo;
use App\Enums\PublicoAlvo;
use App\Enums\StatusPrograma;
use App\Enums\TipoValor;

/**
 * Traduz diferenças entre versões de um programa para linhas legíveis
 * (rótulo do campo, valor anterior, valor novo).
 */
class DescricaoAlteracao
{
    public const ROTULOS = [
        'exercicio' => 'Ano (exercício)',
        'nome' => 'Nome',
        'grupo' => 'Programa maior',
        'descricao' => 'O que é',
        'como_participar' => 'Como participar',
        'bases_legais' => 'Base legal',
        'ano_criacao' => 'Ano de criação',
        'mecanismo' => 'Como o recurso chega',
        'publico_alvo' => 'Público-alvo',
        'fonte_recurso' => 'Fonte do recurso',
        'qtd_atendidos' => 'Quantidade de atendidos',
        'unidade_atendidos' => 'Atendidos são',
        'qtd_beneficios' => 'Benefícios pagos',
        'detalhe_atendidos' => 'Detalhamento',
        'tipo_valor' => 'Tipo de valor',
        'valor' => 'Valor no ano',
        'valor_total_vigencia' => 'Valor total',
        'vigencia_anos' => 'Vigência (anos)',
        'nota_publica' => 'Nota para o cidadão',
        'observacao_interna' => 'Observação interna',
        'status' => 'Situação',
        'publicado_em' => 'Publicado em',
        'secretaria_id' => 'Secretaria',
        'slug' => 'Endereço da página',
    ];

    /** @return list<array{campo: string, de: string, para: string}> */
    public static function linhas(?array $diferencas): array
    {
        $linhas = [];

        foreach ($diferencas ?? [] as $campo => $par) {
            $linhas[] = [
                'campo' => self::ROTULOS[$campo] ?? $campo,
                'de' => self::valor($campo, $par['de'] ?? null),
                'para' => self::valor($campo, $par['para'] ?? null),
            ];
        }

        return $linhas;
    }

    public static function valor(string $campo, mixed $valor): string
    {
        if (is_string($valor) && in_array($campo, ['bases_legais', 'publico_alvo'], true)) {
            $valor = json_decode($valor, true);
        }

        if ($valor === null || $valor === '' || $valor === []) {
            return '(vazio)';
        }

        return match ($campo) {
            'valor', 'valor_total_vigencia' => Formato::moeda($valor),
            'mecanismo' => Mecanismo::tryFrom($valor)?->getLabel() ?? $valor,
            'fonte_recurso' => FonteRecurso::tryFrom($valor)?->getLabel() ?? $valor,
            'tipo_valor' => TipoValor::tryFrom($valor)?->getLabel() ?? $valor,
            'status' => StatusPrograma::tryFrom($valor)?->getLabel() ?? $valor,
            'publico_alvo' => collect($valor)->map(fn ($p) => PublicoAlvo::tryFrom($p)?->getLabel() ?? $p)->join(', '),
            'bases_legais' => collect($valor)->map(fn ($l) => trim(($l['tipo'] ?? '').' '.($l['numero'] ?? '').'/'.($l['ano'] ?? '')))->join('; '),
            default => is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : (string) $valor,
        };
    }
}
