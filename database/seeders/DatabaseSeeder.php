<?php

namespace Database\Seeders;

use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SecretariaSeeder::class,
            ProgramaSeeder::class,
        ]);

        // Usuários de teste, só no ambiente local. Em produção, o administrador é criado com
        // php artisan usuarios:criar e os servidores das secretarias são cadastrados no painel.
        if (app()->environment('local')) {
            User::updateOrCreate(['email' => 'admin@santahelena.test'], [
                'name' => 'Administrador (teste)',
                'password' => 'password',
                'is_admin' => true,
            ]);

            foreach (Secretaria::all() as $secretaria) {
                User::updateOrCreate(['email' => $secretaria->slug.'@santahelena.test'], [
                    'name' => $secretaria->nome_curto.' (teste)',
                    'password' => 'password',
                    'secretaria_id' => $secretaria->id,
                ]);
            }
        }
    }
}
