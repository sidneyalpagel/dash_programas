<?php

namespace Tests\Feature;

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

    public function test_rascunho_nao_aparece_no_site(): void
    {
        $programa = Programa::where('nome', 'Desenvolve Agro')->first();
        $programa->update(['status' => StatusPrograma::Rascunho]);

        $this->get('/programas/desenvolve-agro')->assertNotFound();
        $this->get('/programas')->assertSee('41 programas')->assertDontSee('Desenvolve Agro');
    }
}
