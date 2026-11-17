<?php

namespace Tests\Feature;

use App\Enums\StatusPrograma;
use App\Models\Programa;
use Database\Seeders\ProgramaSeeder;
use Database\Seeders\RelatorioAgriculturaSeeder;
use Database\Seeders\SecretariaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioAgriculturaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SecretariaSeeder::class, ProgramaSeeder::class, RelatorioAgriculturaSeeder::class]);
    }

    private function programa(int $exercicio, string $slug): Programa
    {
        return Programa::where('exercicio', $exercicio)->where('slug', $slug)->firstOrFail();
    }

    public function test_relatorio_substitui_os_numeros_publicados_de_2025(): void
    {
        $pescadores = $this->programa(2025, 'incentivo-para-pescadores');
        $this->assertSame(StatusPrograma::Publicado, $pescadores->status);
        $this->assertSame(39, $pescadores->qtd_atendidos);
        $this->assertEquals(136500, $pescadores->valor);

        $viabiliza = $this->programa(2025, 'viabiliza-agro');
        $this->assertSame('Lei Municipal nº 2.943/2022 e Lei Municipal nº 3.087/2023', $viabiliza->basesLegaisTexto());
        $this->assertEquals(2112500, $viabiliza->valor);

        // Dado de 2024 não vira resultado de 2025: fica "a informar".
        $patios = $this->programa(2025, 'pavimentacao-poliedrica');
        $this->assertNull($patios->valor);
        $this->assertTrue($patios->valorPendente());

        // O relatório não traz o calcário de 2025: o valor do PDF fica.
        $this->assertEquals(3163529, $this->programa(2025, 'distribuicao-de-calcario-ou-cama-de-aviario')->valor);

        $this->assertSame(42, Programa::publicados()->count());
        $this->get('/entenda')->assertOk()->assertSee('R$ 55.690.435,99');
        $this->get('/programas/pavimentacao-poliedrica')->assertOk()
            ->assertSee('Em 2024, foram 40 produtores')
            ->assertDontSee('benefícios pagos');
    }

    public function test_acoes_novas_e_exercicio_2026_entram_como_rascunho(): void
    {
        $novos2025 = Programa::doExercicio(2025)->where('status', StatusPrograma::Rascunho)->pluck('nome');
        $this->assertCount(10, $novos2025);
        $this->assertContains('Aquisição de Caminhão-Pipa', $novos2025);

        $this->assertSame(0, Programa::doExercicio(2026)->publicados()->count());
        $this->assertSame(15, Programa::doExercicio(2026)->count());

        $hora = $this->programa(2026, 'servico-de-hora-maquina');
        $this->assertSame(67, $hora->qtd_atendidos);
        $this->assertEquals(1605000, $hora->valor);

        // O mesmo R$ 1,47 mi (set/2025–set/2026) não é lançado nos dois anos.
        $this->assertNull($this->programa(2026, 'aquisicao-de-alimentos-da-agricultura-familiar-paa')->valor);

        $this->get('/programas')->assertDontSee('Aquisição de Caminhão-Pipa');
    }

    public function test_rodar_de_novo_nao_duplica_nem_desfaz_rascunhos_editados(): void
    {
        $rascunho = $this->programa(2026, 'testes-de-brucelose-e-tuberculose');
        $rascunho->update(['qtd_atendidos' => 110]);
        $total = Programa::count();

        $this->seed(RelatorioAgriculturaSeeder::class);

        $this->assertSame($total, Programa::count());
        $this->assertSame(110, $rascunho->fresh()->qtd_atendidos);
    }
}
