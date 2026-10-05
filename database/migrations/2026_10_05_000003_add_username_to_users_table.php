<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Login passa a ser por nome de usuário. O e-mail fica opcional
 * (usado apenas para recuperar a senha, quando houver SMTP).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 60)->nullable()->after('name');
        });

        // Usuários existentes recebem a parte do e-mail antes do @.
        $usados = [];

        foreach (DB::table('users')->orderBy('id')->get(['id', 'email']) as $user) {
            $base = Str::of(Str::before($user->email, '@'))->ascii()->lower()->replaceMatches('/[^a-z0-9._-]/', '')->value() ?: 'usuario';
            $username = $base;
            $i = 2;

            while (in_array($username, $usados, true)) {
                $username = $base.$i++;
            }

            $usados[] = $username;
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 60)->nullable(false)->unique()->change();
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
