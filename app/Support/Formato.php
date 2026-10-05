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

    /** Forma curta para leitura rápida: R$ 60,1 milhões, R$ 950 mil. */
    public static function moedaCurta(float|string|null $valor): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        $valor = (float) $valor;

        if ($valor >= 1_000_000) {
            $casas = $valor >= 100_000_000 ? 0 : 1;
            $milhoes = round($valor / 1_000_000, $casas);
            $texto = number_format($milhoes, $casas, ',', '.');

            return 'R$ '.$texto.' '.($milhoes < 2 ? 'milhão' : 'milhões');
        }

        if ($valor >= 1_000) {
            return 'R$ '.number_format($valor / 1_000, 0, ',', '.').' mil';
        }

        return self::moeda($valor);
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
