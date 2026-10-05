<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Cria um administrador pelo terminal (necessário para o primeiro acesso).
 * Os servidores das secretarias são cadastrados no painel, em Administração → Usuários.
 */
class CriarUsuario extends Command
{
    protected $signature = 'usuarios:criar {--admin : Mantido por compatibilidade; o comando sempre cria administrador}';

    protected $description = 'Cria um administrador do painel (servidores das secretarias são cadastrados no painel)';

    public function handle(): int
    {
        $nome = text('Nome', required: true);
        $email = text('E-mail', required: true, validate: fn (string $valor) => Validator::make(
            ['email' => $valor],
            ['email' => 'email|unique:users,email'],
        )->errors()->first('email') ?: null);
        $senha = password('Senha (mínimo 8 caracteres)', required: true, validate: fn (string $valor) => strlen($valor) < 8 ? 'Use pelo menos 8 caracteres.' : null);

        User::create([
            'name' => $nome,
            'email' => $email,
            'password' => $senha,
            'is_admin' => true,
        ]);

        $this->info("Administrador {$email} criado. Cadastre os servidores das secretarias no painel, em Administração → Usuários.");

        return self::SUCCESS;
    }
}
