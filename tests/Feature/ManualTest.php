<?php

namespace Tests\Feature;

use App\Filament\Pages\ManualDoCadastrador;
use App\Models\Secretaria;
use App\Models\User;
use Database\Seeders\SecretariaSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_abre_no_painel_para_quem_cadastra(): void
    {
        $this->seed(SecretariaSeeder::class);
        Filament::setCurrentPanel('admin');

        $servidor = User::factory()->create([
            'secretaria_id' => Secretaria::where('slug', 'educacao')->value('id'),
        ]);

        $this->actingAs($servidor)
            ->get(ManualDoCadastrador::getUrl())
            ->assertOk()
            ->assertSee('Manual do cadastrador')
            ->assertSee('href="#acesso-ao-painel"', false)
            ->assertSee('id="virada-de-ano"', false)
            ->assertSee('Você confere e publica: rascunho não aparece no site')
            ->assertSee('Pré-visualizar')
            ->assertDontSee('[[diagrama-fluxo]]')
            ->assertDontSee('Enviar para revisão');
    }

    public function test_manual_exige_login(): void
    {
        $this->get('/admin/manual')->assertRedirect();
    }
}
