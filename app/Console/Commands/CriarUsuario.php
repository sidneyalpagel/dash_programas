<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\NomeUsuario;
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
        $usuario = mb_strtolower(trim(text('Usuário (para entrar no painel)', required: true, validate: fn (string $valor) => Validator::make(
            ['username' => mb_strtolower(trim($valor))],
            ['username' => ['min:3', 'max:60', 'regex:'.NomeUsuario::REGRA, 'unique:users,username']],
            ['username.regex' => 'Use letras minúsculas, números, ponto, hífen ou sublinhado.'],
        )->errors()->first('username') ?: null)));
        $email = text('E-mail (opcional, para recuperar a senha)', validate: fn (string $valor) => $valor === '' ? null : (Validator::make(
            ['email' => $valor],
            ['email' => 'email|unique:users,email'],
        )->errors()->first('email') ?: null));
        $senha = password('Senha (mínimo 8 caracteres)', required: true, validate: fn (string $valor) => strlen($valor) < 8 ? 'Use pelo menos 8 caracteres.' : null);

        User::create([
            'name' => $nome,
            'username' => $usuario,
            'email' => $email ?: null,
            'password' => $senha,
            'is_admin' => true,
        ]);

        $this->info("Administrador \"{$usuario}\" criado. Cadastre os servidores das secretarias no painel, em Administração → Usuários.");

        return self::SUCCESS;
    }
}
