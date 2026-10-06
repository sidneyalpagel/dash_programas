<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vários exercícios convivendo: o mesmo programa existe em 2025 e 2026 com o
 * mesmo endereço (slug), e o administrador escolhe qual ano o site exibe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->string('chave', 100)->primary();
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        Schema::table('programas', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['exercicio', 'slug']);
            $table->unique(['secretaria_id', 'exercicio', 'nome']);
        });

        // Fixa o ano que já está no ar: publicar o primeiro programa do ano
        // seguinte não pode trocar o site sozinho.
        $atual = DB::table('programas')->where('status', 'publicado')->max('exercicio');

        if ($atual) {
            DB::table('configuracoes')->insert([
                'chave' => 'exercicio_exibicao',
                'valor' => (string) $atual,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->dropUnique(['secretaria_id', 'exercicio', 'nome']);
            $table->dropUnique(['exercicio', 'slug']);
            $table->unique('slug');
        });

        Schema::dropIfExists('configuracoes');
    }
};
