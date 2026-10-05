<?php

namespace Tests\Unit;

use App\Support\Formato;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FormatoTest extends TestCase
{
    public static function valoresDigitados(): array
    {
        return [
            'padrão brasileiro' => ['1.234.567,89', 1234567.89],
            'sem milhar' => ['950,5', 950.5],
            'milhar sem centavos' => ['1.832.464', 1832464.0],
            'ponto decimal' => ['1234.56', 1234.56],
            'com prefixo' => ['R$ 3.500,00', 3500.0],
            'vazio' => ['', null],
            'inválido' => ['abc', null],
        ];
    }

    #[DataProvider('valoresDigitados')]
    public function test_le_valores_em_reais(string $texto, ?float $esperado): void
    {
        $this->assertSame($esperado, Formato::lerMoeda($texto));
    }

    public function test_forma_curta(): void
    {
        $this->assertSame('R$ 60,1 milhões', Formato::moedaCurta(60136552.72));
        $this->assertSame('R$ 1,8 milhão', Formato::moedaCurta(1832464.53));
        $this->assertSame('R$ 2,0 milhões', Formato::moedaCurta(1980000));
        $this->assertSame('R$ 631 mil', Formato::moedaCurta(631138.05));
    }
}
