<?php

namespace Tests\Feature;

use App\Filament\Resources\PagamentoServidors\PagamentoServidorResource;
use App\Models\PagamentoServidor;
use App\Models\Secretaria;
use App\Models\User;
use Database\Seeders\PagamentoServidorSeeder;
use Database\Seeders\ProgramaSeeder;
use Database\Seeders\SecretariaSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagamentosServidoresTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SecretariaSeeder::class, ProgramaSeeder::class, PagamentoServidorSeeder::class]);
    }

    public function test_pagina_mostra_as_verbas_por_ano_com_total(): void
    {
        $this->get(route('servidores'))
            ->assertOk()
            ->assertSeeInOrder(['2026', 'Horas extras excedentes do banco de horas', '2025', 'Licença-prêmio'])
            ->assertSee('R$ 7.813.172,40')
            ->assertSee('R$ 624.644,43')
            ->assertSee('R$ 5.304.285,81')
            // A mesma pessoa pode receber mais de uma verba: o total de servidores não é somado.
            ->assertDontSee('392');
    }

    public function test_verba_oculta_nao_aparece(): void
    {
        PagamentoServidor::factory()->rascunho()->create(['verba' => 'Verba ainda em conferência']);

        $this->get(route('servidores'))->assertDontSee('Verba ainda em conferência');
    }

    public function test_pagamentos_nao_entram_no_total_dos_programas(): void
    {
        $this->get('/')->assertSeeInOrder(['R$ 60,14', 'milhões']);
        $this->get('/entenda')->assertSee('R$ 60.136.552,72')->assertSee(route('servidores'), false);
    }

    public function test_menu_do_topo_tem_o_link_e_marca_a_pagina_atual(): void
    {
        $this->get('/')->assertSee('href="'.route('servidores').'"', false);
        $this->get(route('servidores'))->assertSeeInOrder(['aria-current="page"', '>Servidores</a>'], false);
    }

    public function test_somente_administrador_mantem_os_pagamentos(): void
    {
        Filament::setCurrentPanel('admin');

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(PagamentoServidorResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Licença-prêmio');

        $this->actingAs(User::factory()->create([
            'secretaria_id' => Secretaria::where('slug', 'educacao')->value('id'),
        ]))
            ->get(PagamentoServidorResource::getUrl('index'))
            ->assertForbidden();
    }
}
