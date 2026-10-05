<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\Secretaria;
use App\Models\User;
use Database\Seeders\SecretariaSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UsuariosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SecretariaSeeder::class);
        Filament::setCurrentPanel('admin');
    }

    public function test_administrador_cadastra_servidor_de_secretaria_pelo_painel(): void
    {
        $educacao = Secretaria::where('slug', 'educacao')->first();

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Servidora da Educação',
                'username' => 'servidora.educacao',
                'secretaria_id' => $educacao->id,
                'password' => 'senha123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $usuario = User::where('username', 'servidora.educacao')->first();

        $this->assertFalse($usuario->isAdmin());
        $this->assertTrue($usuario->secretaria->is($educacao));
        $this->assertTrue(Hash::check('senha123', $usuario->password));
    }

    public function test_servidor_precisa_de_secretaria(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Sem secretaria',
                'username' => 'sem.secretaria',
                'password' => 'senha123',
            ])
            ->call('create')
            ->assertHasFormErrors(['secretaria_id' => 'required']);
    }

    public function test_servidor_de_secretaria_nao_gerencia_usuarios(): void
    {
        $this->actingAs(User::factory()->create([
            'secretaria_id' => Secretaria::where('slug', 'esportes')->value('id'),
        ]));

        $this->get(UserResource::getUrl('index'))->assertForbidden();
        $this->get(UserResource::getUrl('create'))->assertForbidden();
    }

    public function test_login_e_feito_pelo_nome_de_usuario(): void
    {
        $usuario = User::factory()->create([
            'username' => 'maria.silva',
            'email' => 'maria@exemplo.test',
            'password' => 'senha123',
            'secretaria_id' => Secretaria::where('slug', 'educacao')->value('id'),
        ]);

        Livewire::test(Login::class)
            ->fillForm(['username' => 'maria@exemplo.test', 'password' => 'senha123'])
            ->call('authenticate')
            ->assertHasFormErrors(['username']);

        $this->assertGuest();

        Livewire::test(Login::class)
            ->fillForm(['username' => ' Maria.Silva ', 'password' => 'senha123'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_comando_cria_somente_administrador(): void
    {
        $this->artisan('usuarios:criar')
            ->expectsQuestion('Nome', 'Admin')
            ->expectsQuestion('Usuário (para entrar no painel)', 'Joao.Admin')
            ->expectsQuestion('E-mail (opcional, para recuperar a senha)', '')
            ->expectsQuestion('Senha (mínimo 8 caracteres)', 'senha123')
            ->assertSuccessful();

        $this->assertTrue(User::where('username', 'joao.admin')->first()->isAdmin());
    }
}
