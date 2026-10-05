<?php

namespace App\Console\Commands;

use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CriarUsuario extends Command
{
    protected $signature = 'usuarios:criar {--admin : Cria um administrador (revisa e publica tudo)}';

    protected $description = 'Cria um usuário do painel: administrador ou servidor de uma secretaria';

    public function handle(): int
    {
        $nome = text('Nome', required: true);
        $email = text('E-mail', required: true, validate: fn (string $valor) => Validator::make(
            ['email' => $valor],
            ['email' => 'email|unique:users,email'],
        )->errors()->first('email') ?: null);
        $senha = password('Senha (mínimo 8 caracteres)', required: true, validate: fn (string $valor) => strlen($valor) < 8 ? 'Use pelo menos 8 caracteres.' : null);

        $secretariaId = null;

        if (! $this->option('admin')) {
            $secretariaId = select('Secretaria', Secretaria::pluck('nome', 'id')->all());
        }

        User::create([
            'name' => $nome,
            'email' => $email,
            'password' => $senha,
            'is_admin' => (bool) $this->option('admin'),
            'secretaria_id' => $secretariaId,
        ]);

        $this->info("Usuário {$email} criado.");

        return self::SUCCESS;
    }
}
