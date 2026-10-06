<?php

namespace App\Support;

/**
 * Formatação de números no padrão brasileiro, usada no painel e no site.
 */
class Formato
{
    /** R$ 1.234.567,89 */
    public static function moeda(float|string|null $valor, int $casas = 2): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        return 'R$ '.number_format((float) $valor, $casas, ',', '.');
    }

    /**
     * Forma curta para leitura rápida: R$ 60,14 milhões, R$ 1,03 milhão, R$ 950 mil.
     * Milhões usam até duas casas (sem zeros à direita) para que a soma de valores
     * arredondados não pareça errada: 950 mil + 1,03 milhão = 1,98 milhão.
     */
    public static function moedaCurta(float|string|null $valor, bool $abreviado = false): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        $partes = self::partesCurtas((float) $valor);

        if ($partes['unidade'] === null) {
            return self::moeda($valor);
        }

        $unidade = $abreviado ? $partes['abreviada'] : $partes['unidade'];

        return 'R$ '.number_format($partes['numero'], $partes['casas'], ',', '.').' '.$unidade;
    }

    /**
     * Número, casas decimais e unidade da forma curta. Usado também pelos números animados.
     *
     * @return array{numero: float, casas: int, unidade: ?string, abreviada: ?string}
     */
    public static function partesCurtas(float $valor): array
    {
        $mil = round($valor / 1_000);

        if ($valor >= 1_000 && $mil < 1_000) {
            return ['numero' => $mil, 'casas' => 0, 'unidade' => 'mil', 'abreviada' => 'mil'];
        }

        if ($valor >= 1_000) {
            $maximo = $valor >= 100_000_000 ? 0 : 2;
            $milhoes = round($valor / 1_000_000, $maximo);
            $casas = $maximo;

            while ($casas > 0 && round($milhoes, $casas - 1) == $milhoes) {
                $casas--;
            }

            return [
                'numero' => $milhoes,
                'casas' => $casas,
                'unidade' => $milhoes < 2 ? 'milhão' : 'milhões',
                'abreviada' => 'mi',
            ];
        }

        return ['numero' => $valor, 'casas' => 2, 'unidade' => null, 'abreviada' => null];
    }

    public static function numero(int|float|null $valor, int $casas = 0): string
    {
        return $valor === null ? '—' : number_format($valor, $casas, ',', '.');
    }

    public static function percentual(float $fracao, int $casas = 1): string
    {
        return number_format($fracao * 100, $casas, ',', '.').'%';
    }

    /** Converte "1.234,56" (ou "1234.56") em 1234.56. */
    public static function lerMoeda(string|int|float|null $texto): ?float
    {
        if ($texto === null || $texto === '') {
            return null;
        }

        if (is_int($texto) || is_float($texto)) {
            return (float) $texto;
        }

        $texto = preg_replace('/[^\d,.\-]/', '', $texto);

        if (str_contains($texto, ',')) {
            $texto = str_replace(['.', ','], ['', '.'], $texto);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $texto)) {
            // "1.234.567" sem centavos: pontos são separadores de milhar.
            $texto = str_replace('.', '', $texto);
        }

        return is_numeric($texto) ? round((float) $texto, 2) : null;
    }
}
