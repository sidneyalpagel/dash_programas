<?php

namespace Tests\Feature;

use App\Enums\StatusPrograma;
use App\Filament\Resources\Programas\Pages\CreatePrograma;
use App\Filament\Resources\Programas\Pages\EditPrograma;
use App\Filament\Resources\Programas\Pages\ListProgramas;
use App\Models\Configuracao;
use App\Models\Programa;
use App\Models\Secretaria;
use App\Models\User;
use App\Support\Panorama;
use Database\Seeders\ProgramaSeeder;
use Database\Seeders\SecretariaSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Vários exercícios convivendo: virada de ano, anos anteriores e escolha do ano exibido. */
class ExerciciosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SecretariaSeeder::class, ProgramaSeeder::class]);
        Configuracao::definir(Configuracao::EXERCICIO_EXIBICAO, 2025);

        $this->admin = User::factory()->create(['is_admin' => true]);
        Filament::setCurrentPanel('admin');
    }

    private function programa(string $nome, int $exercicio = 2025): Programa
    {
        return Programa::where('nome', $nome)->where('exercicio', $exercicio)->firstOrFail();
    }

    public function test_copia_para_o_novo_ano_vira_rascunho_sem_valores(): void
    {
        $original = $this->programa('Desenvolve Agro');
        $copia = $original->copiarPara(2026);

        $this->assertSame(2026, $copia->exercicio);
        $this->assertSame('desenvolve-agro', $copia->slug, 'mesmo endereço, outro ano');
        $this->assertSame(StatusPrograma::Rascunho, $copia->status);
        $this->assertSame($original->descricao, $copia->descricao);
        $this->assertSame($original->bases_legais, $copia->bases_legais);
        $this->assertSame($original->vigencia_anos, $copia->vigencia_anos);
        $this->assertNull($copia->valor);
        $this->assertNull($copia->valor_total_vigencia);
        $this->assertNull($copia->qtd_atendidos);
        $this->assertContains('Valor', $copia->pendencias());
        $this->assertTrue($copia->historicos()->where('acao', 'copiado de 2025')->exists());

        // O original não muda e não dá para copiar duas vezes.
        $this->assertEquals(631138.05, (float) $original->fresh()->valor);
        $this->assertNull($original->copiarPara(2026));
    }

    public function test_copia_em_lote_pelo_painel(): void
    {
        $this->actingAs($this->admin);
        $agricultura = Programa::where('exercicio', 2025)->where('secretaria_id', Secretaria::where('slug', 'agricultura')->value('id'))->get();

        Livewire::test(ListProgramas::class)
            ->callTableBulkAction('copiarParaExercicio', $agricultura, data: ['exercicio' => 2026])
            ->assertNotified();

        $this->assertSame(11, Programa::where('exercicio', 2026)->count());
        $this->assertSame(42, Programa::where('exercicio', 2025)->count());

        // Repetir não duplica.
        Livewire::test(ListProgramas::class)
            ->callTableBulkAction('copiarParaExercicio', $agricultura, data: ['exercicio' => 2026]);

        $this->assertSame(11, Programa::where('exercicio', 2026)->count());
    }

    public function test_publicar_um_programa_do_ano_novo_nao_troca_o_site(): void
    {
        $copia = $this->programa('Desenvolve Agro')->copiarPara(2026);
        $copia->update(['valor_total_vigencia' => 7000000, 'status' => StatusPrograma::Publicado]);

        $this->assertSame(2025, Panorama::exercicioExibido());
        $this->get('/')->assertOk()->assertSee('42 programas');

        // O ano novo fica acessível pelo endereço com o ano.
        $this->get('/2026')->assertOk()->assertSee('1 programa</strong>', false)->assertSee('Você está vendo o exercício');
        $this->get('/2026/programas/desenvolve-agro')->assertOk()->assertSee('R$ 700.000,00');
        $this->get('/programas/desenvolve-agro')->assertOk()->assertSee('R$ 631.138,05');
    }

    public function test_administrador_escolhe_o_ano_exibido(): void
    {
        $copia = $this->programa('Desenvolve Agro')->copiarPara(2026);
        $copia->update(['status' => StatusPrograma::Publicado]);

        $this->actingAs($this->admin);

        Livewire::test(ListProgramas::class)
            ->callAction('exercicioNoSite', data: ['exercicio' => 2026])
            ->assertNotified();

        $this->assertSame(2026, Panorama::exercicioExibido());
        $this->get('/')->assertSee('1 programa</strong>', false);
        $this->get('/2025')->assertOk()->assertSee('42 programas');
        $this->get('/2026')->assertRedirect('/');
    }

    public function test_servidor_nao_escolhe_o_ano_exibido(): void
    {
        $this->actingAs(User::factory()->create([
            'secretaria_id' => Secretaria::where('slug', 'agricultura')->value('id'),
        ]));

        Livewire::test(ListProgramas::class)->assertActionHidden('exercicioNoSite');
    }

    public function test_anos_anteriores_e_seletor(): void
    {
        $antigo = $this->programa('Educa Mais Santa Helena')->copiarPara(2024);
        $antigo->update(['valor' => 2500000, 'status' => StatusPrograma::Publicado]);

        $inicio = $this->get('/')->assertOk();
        $inicio->assertSee('Escolher exercício', false);
        $inicio->assertSee('href="'.url('/2024').'"', false);

        $this->get('/2024/programas')->assertOk()->assertSee('1 programa');
        $this->get('/2024/programas/educa-mais-santa-helena')->assertOk()->assertSee('R$ 2.500.000,00');

        // Ano sem nada publicado não existe.
        $this->get('/2019')->assertNotFound();
        $this->get('/2019/programas')->assertNotFound();

        // Os links internos da página de 2024 continuam em 2024.
        $this->get('/2024/programas')->assertSee('href="'.url('/2024/programas/educa-mais-santa-helena').'"', false);
    }

    public function test_ano_nao_pode_ser_trocado_na_edicao(): void
    {
        $programa = $this->programa('Incentivo para Pescadores');
        $this->actingAs($this->admin);

        Livewire::test(EditPrograma::class, ['record' => $programa->getKey()])
            ->assertFormFieldIsDisabled('exercicio')
            ->fillForm(['exercicio' => 2030, 'descricao' => 'Nova descrição.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $programa->refresh();
        $this->assertSame(2025, $programa->exercicio);
        $this->assertSame('Nova descrição.', $programa->descricao);
    }

    public function test_nome_repetido_no_mesmo_ano_e_secretaria_e_recusado(): void
    {
        $this->actingAs($this->admin);
        $agricultura = Secretaria::where('slug', 'agricultura')->value('id');

        $dados = [
            'secretaria_id' => $agricultura,
            'nome' => 'Viabiliza Agro',
            'mecanismo' => 'incentivo_produtivo',
            'publico_alvo' => ['produtores_rurais'],
            'tipo_valor' => 'anual',
            'valor' => '1.000,00',
            'fonte_recurso' => 'municipal',
        ];

        Livewire::test(CreatePrograma::class)
            ->fillForm($dados + ['exercicio' => 2025])
            ->call('create')
            ->assertHasFormErrors(['nome' => 'unique']);

        // Em outro ano, pode.
        Livewire::test(CreatePrograma::class)
            ->fillForm($dados + ['exercicio' => 2026])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_resumo_do_painel_nao_soma_anos_diferentes(): void
    {
        $copia = $this->programa('Desenvolve Agro')->copiarPara(2026);
        $copia->update(['valor_total_vigencia' => 7000000, 'status' => StatusPrograma::Publicado]);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk();

        Livewire::test(\App\Filament\Widgets\ResumoProgramas::class)
            ->assertSee('Publicados em 2025')
            ->assertSee('R$ 60,14 milhões');
    }
}
