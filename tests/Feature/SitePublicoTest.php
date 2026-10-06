<?php

namespace Tests\Feature;

use App\Enums\Mecanismo;
use App\Enums\StatusPrograma;
use App\Models\Programa;
use Database\Seeders\ProgramaSeeder;
use Database\Seeders\SecretariaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePublicoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SecretariaSeeder::class, ProgramaSeeder::class]);
    }

    public function test_carga_inicial_bate_com_o_pdf(): void
    {
        $this->assertSame(42, Programa::count());
        $this->assertEqualsWithDelta(60136552.72, (float) Programa::sum('valor'), 0.001);
        $this->assertEqualsWithDelta(631138.05, (float) Programa::where('nome', 'Desenvolve Agro')->value('valor'), 0.001);
        $this->assertEqualsWithDelta(1391334.56, (float) Programa::where('nome', 'Viabiliza Agro')->value('valor'), 0.001);
    }

    public function test_paginas_publicas_abrem(): void
    {
        $this->get('/')->assertOk()->assertSeeInOrder(['R$ 60,1', 'milhões'])->assertSee('39,5%')->assertSee('Por trás dos números');
        $this->get('/programas')->assertOk()->assertSee('42 programas');
        $this->get('/programas?perfil=familias')->assertOk()->assertSee('10 programas');
        $this->get('/programas?busca=transporte')->assertOk()->assertSee('2 programas');
        $this->get('/programas/desenvolve-agro')->assertOk()->assertSee('Valor anualizado');
        $this->get('/entenda')->assertOk();
    }

    public function test_ficha_explica_o_tipo_sem_citar_exemplos_de_outros_programas(): void
    {
        $fomento = Programa::where('nome', 'Programa Municipal de Fomento ao Esporte')->first();

        $this->get(route('programas.show', $fomento))
            ->assertOk()
            ->assertSee('Como o dinheiro chega')
            ->assertSee(Mecanismo::IncentivoProdutivo->explicacaoNaFicha())
            ->assertDontSee('produtores rurais');
    }

    public function test_ficha_sem_custo_explica_a_origem_do_recurso(): void
    {
        $credito = Programa::where('nome', 'Fomento Paraná - Micro Fácil')->first();

        $this->get(route('programas.show', $credito))
            ->assertOk()
            ->assertSee('Como funciona')
            ->assertDontSee('Como o dinheiro chega')
            ->assertSee('O recurso é do Governo do Estado. Não há gasto direto do Município.')
            ->assertSee('para o Município em 2025')
            ->assertDontSee('Origem do dinheiro');
    }

    public function test_media_informa_a_unidade_e_some_com_um_so_atendido(): void
    {
        $fomento = Programa::where('nome', 'Programa Municipal de Fomento ao Esporte')->first();
        $tendas = Programa::where('nome', 'Assuntos Comunitários - Tendas')->first();

        $this->get(route('programas.show', $fomento))
            ->assertSee('Valor do ano dividido por 7 associações.')
            ->assertSee('Outros programas da Secretaria Municipal de Esportes e Lazer');

        $this->get(route('programas.show', $tendas))
            ->assertDontSee('em média')
            ->assertSee('menos de 0,1% de tudo');
    }

    public function test_valor_nao_informado_nao_aparece_como_sem_custo(): void
    {
        $bolsa = Programa::where('nome', 'Programa Bolsa Atleta Municipal')->first();
        $bolsa->update(['valor' => null]);

        $this->get(route('programas.show', $bolsa))
            ->assertOk()
            ->assertSee('A informar')
            ->assertDontSee('Sem custo direto');

        $this->get('/')->assertSee('5 deles funcionam sem custo direto');
    }

    public function test_perfil_nao_herda_busca_nem_grupo(): void
    {
        // Quem chega pela ficha ("Parte do Fomento Paraná") e depois escolhe um perfil
        // não pode continuar preso à busca anterior.
        $resposta = $this->get('/programas?busca=Fomento+Paran%C3%A1&secretaria=desenvolvimento-economico')
            ->assertOk()
            ->assertSee('3 programas')
            ->assertSee('Busca: &quot;Fomento Paraná&quot;', false);

        $linkPerfil = route('programas.index', ['perfil' => 'estudantes', 'secretaria' => 'desenvolvimento-economico']);
        $resposta->assertSee('href="'.e($linkPerfil).'"', false);

        $this->get('/programas?grupo=Fomento+Paran%C3%A1&perfil=mulheres')
            ->assertOk()
            ->assertSee('1 programa');
    }

    public function test_filtro_por_grupo(): void
    {
        $this->get('/programas?grupo=Renda+Santa+Helena')
            ->assertOk()
            ->assertSee('6 programas')
            ->assertSee('Programa: Renda Santa Helena');

        // Grupo inexistente é ignorado em vez de zerar a lista.
        $this->get('/programas?grupo=Inexistente')->assertOk()->assertSee('42 programas');

        $progredir = Programa::where('nome', 'Progredir')->first();
        $this->get(route('programas.show', $progredir))
            ->assertSee(e(route('programas.index', ['grupo' => 'Renda Santa Helena'])), false);
    }

    public function test_rascunho_nao_aparece_no_site(): void
    {
        $programa = Programa::where('nome', 'Desenvolve Agro')->first();
        $programa->update(['status' => StatusPrograma::Rascunho]);

        $this->get('/programas/desenvolve-agro')->assertNotFound();
        $this->get('/programas')->assertSee('41 programas')->assertDontSee('Desenvolve Agro');
    }
}
