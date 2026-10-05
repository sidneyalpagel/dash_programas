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

    public function test_alteracao_em_programa_publicado_aguarda_aprovacao(): void
    {
        $programa = $this->programa('Incentivo para Apicultores');

        $this->actingAs($this->agricultura);

        Livewire::test(EditPrograma::class, ['record' => $programa->getRouteKey()])
            ->fillForm([
                'como_participar' => 'Apicultores cadastrados na Secretaria de Agricultura.',
                'valor' => '200.000,00',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $programa->refresh();

        // O site continua com a versão publicada.
        $this->assertNull($programa->como_participar);
        $this->assertEquals(168000, (float) $programa->valor);
        $this->assertTrue($programa->temAlteracaoPendente());
        $this->assertSame(
            ['como_participar', 'valor'],
            array_keys($programa->diferencasPropostas()),
        );
        $this->get('/programas/incentivo-para-apicultores')->assertDontSee('Apicultores cadastrados');

        // O administrador aprova.
        $this->actingAs($this->admin);

        Livewire::test(EditPrograma::class, ['record' => $programa->getRouteKey()])
            ->callAction('revisar');

        $programa->refresh();

        $this->assertFalse($programa->temAlteracaoPendente());
        $this->assertEquals(200000, (float) $programa->valor);
        $this->get('/programas/incentivo-para-apicultores')->assertSee('Apicultores cadastrados');
        $this->assertTrue($programa->historicos()->where('acao', 'alteração aprovada')->exists());
    }

    public function test_administrador_pode_descartar_proposta(): void
    {
        $programa = $this->programa('Incentivo para Pescadores');
        $programa->proporAlteracoes(['nome' => 'Outro nome']);

        $this->actingAs($this->admin);

        Livewire::test(EditPrograma::class, ['record' => $programa->getRouteKey()])
            ->callAction('revisar', arguments: ['descartar' => true]);

        $programa->refresh();
        $this->assertFalse($programa->temAlteracaoPendente());
        $this->assertSame('Incentivo para Pescadores', $programa->nome);
    }

    public function test_programa_novo_vai_de_rascunho_a_publicado(): void
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

        Livewire::test(EditPrograma::class, ['record' => $programa->getRouteKey()])
            ->assertActionHidden('publicar')
            ->callAction('enviarRevisao');

        $this->assertSame(StatusPrograma::EmRevisao, $programa->refresh()->status);

        $this->actingAs($this->admin);

        Livewire::test(EditPrograma::class, ['record' => $programa->getRouteKey()])
            ->callAction('publicar');

        $this->assertSame(StatusPrograma::Publicado, $programa->refresh()->status);
        $this->get('/programas/programa-de-teste-rural')->assertOk()->assertSee('R$ 250.000,00');
    }
}
