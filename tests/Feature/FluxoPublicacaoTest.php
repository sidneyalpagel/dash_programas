<?php

namespace Tests\Feature;

use App\Enums\StatusPrograma;
use App\Filament\Resources\Programas\Pages\CreatePrograma;
use App\Filament\Resources\Programas\Pages\EditPrograma;
use App\Filament\Resources\Programas\Pages\ListProgramas;
use App\Models\Programa;
use App\Models\Secretaria;
use App\Models\User;
use Database\Seeders\ProgramaSeeder;
use Database\Seeders\SecretariaSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FluxoPublicacaoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $agricultura;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SecretariaSeeder::class, ProgramaSeeder::class]);

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->agricultura = User::factory()->create([
            'secretaria_id' => Secretaria::where('slug', 'agricultura')->value('id'),
        ]);

        Filament::setCurrentPanel('admin');
    }

    private function programa(string $nome): Programa
    {
        return Programa::where('nome', $nome)->firstOrFail();
    }

    public function test_servidor_so_ve_programas_da_propria_secretaria(): void
    {
        $this->actingAs($this->agricultura);

        Livewire::test(ListProgramas::class)
            ->assertCountTableRecords(11)
            ->assertCanSeeTableRecords([$this->programa('Desenvolve Agro')])
            ->assertCanNotSeeTableRecords([$this->programa('Merenda Escolar')]);

        // A consulta já é filtrada por secretaria: o programa nem "existe" para ele.
        $this->get(EditPrograma::getUrl(['record' => $this->programa('Merenda Escolar')]))->assertNotFound();
    }

    public function test_usuario_sem_secretaria_nao_entra_no_painel(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_edicao_de_programa_publicado_vai_direto_ao_site(): void
    {
        $programa = $this->programa('Incentivo para Apicultores');

        $this->actingAs($this->agricultura);

        Livewire::test(EditPrograma::class, ['record' => $programa->getKey()])
            ->fillForm([
                'como_participar' => 'Apicultores cadastrados na Secretaria de Agricultura.',
                'valor' => '200.000,00',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Alterações salvas e já publicadas no site.');

        $programa->refresh();

        $this->assertEquals(200000, (float) $programa->valor);
        $this->assertTrue($programa->estaPublicado());
        $this->get('/programas/incentivo-para-apicultores')->assertSee('Apicultores cadastrados');

        $alteracao = $programa->historicos()->where('acao', 'editado')->first();
        $this->assertSame(['como_participar', 'valor'], array_keys($alteracao->alteracoes));
        $this->assertSame($this->agricultura->id, $alteracao->user_id);
    }

    public function test_secretaria_cadastra_confere_e_publica_sozinha(): void
    {
        $this->actingAs($this->agricultura);

        Livewire::test(CreatePrograma::class)
            ->fillForm([
                'exercicio' => 2025,
                'nome' => 'Programa de Teste Rural',
                'mecanismo' => 'incentivo_produtivo',
                'descricao' => 'Descrição.',
                'publico_alvo' => ['produtores_rurais'],
                'tipo_valor' => 'anualizado',
                'valor_total_vigencia' => '1.000.000,00',
                'vigencia_anos' => 4,
                'fonte_recurso' => 'municipal',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $programa = $this->programa('Programa de Teste Rural');

        $this->assertSame($this->agricultura->secretaria_id, $programa->secretaria_id);
        $this->assertSame(StatusPrograma::Rascunho, $programa->status);
        $this->assertEquals(250000, (float) $programa->valor);
        $this->get('/programas/programa-de-teste-rural')->assertNotFound();

        // Pré-visualização: a ficha como ficará, só para quem edita.
        $this->get(route('previa.programa', $programa))
            ->assertOk()
            ->assertSee('Pré-visualização')
            ->assertSee('R$ 250.000,00');

        Livewire::test(EditPrograma::class, ['record' => $programa->getKey()])
            ->assertActionVisible('previa')
            ->callAction('publicar')
            ->assertNotified('Programa publicado no site.');

        $this->assertSame(StatusPrograma::Publicado, $programa->refresh()->status);
        $this->get('/programas/programa-de-teste-rural')->assertOk()->assertSee('R$ 250.000,00');

        // E pode tirar do site.
        Livewire::test(EditPrograma::class, ['record' => $programa->getKey()])
            ->assertActionHidden('previa')
            ->callAction('despublicar');

        $this->assertSame(StatusPrograma::Rascunho, $programa->refresh()->status);
        $this->get('/programas/programa-de-teste-rural')->assertNotFound();
    }

    public function test_previa_exige_login_e_permissao(): void
    {
        $merenda = $this->programa('Merenda Escolar');

        $this->get(route('previa.programa', $merenda))->assertRedirect('/admin/login');

        $this->actingAs($this->agricultura)
            ->get(route('previa.programa', $merenda))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('previa.programa', $merenda))
            ->assertOk();
    }

    public function test_servidor_nao_exclui_programa_que_ja_foi_publicado(): void
    {
        $programa = $this->programa('Incentivo para Pescadores');

        $this->actingAs($this->agricultura);

        Livewire::test(EditPrograma::class, ['record' => $programa->getKey()])
            ->assertActionHidden('delete');
    }
}
