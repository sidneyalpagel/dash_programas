<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secretaria_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('exercicio');
            $table->string('nome');
            $table->string('slug')->unique();
            // Agrupa ações de um mesmo programa-mãe (ex.: "Renda Santa Helena").
            $table->string('grupo')->nullable();

            // O que é e como participar, em linguagem simples.
            $table->text('descricao')->nullable();
            $table->text('como_participar')->nullable();
            $table->json('bases_legais')->nullable();
            $table->unsignedSmallInteger('ano_criacao')->nullable();

            // Classificação.
            $table->string('mecanismo');
            $table->json('publico_alvo')->nullable();
            $table->string('fonte_recurso')->default('municipal');

            // Quem é atendido: pessoas e benefícios ficam separados.
            $table->unsignedInteger('qtd_atendidos')->nullable();
            $table->string('unidade_atendidos', 60)->nullable();
            $table->unsignedInteger('qtd_beneficios')->nullable();
            $table->string('detalhe_atendidos')->nullable();

            // Quanto custa.
            $table->string('tipo_valor')->default('anual');
            $table->decimal('valor', 15, 2)->nullable();
            $table->decimal('valor_total_vigencia', 15, 2)->nullable();
            $table->unsignedTinyInteger('vigencia_anos')->nullable();

            $table->text('nota_publica')->nullable();
            $table->text('observacao_interna')->nullable();

            // Fluxo de publicação.
            $table->string('status')->default('rascunho');
            $table->json('alteracoes_pendentes')->nullable();
            $table->foreignId('atualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('publicado_em')->nullable();
            $table->timestamps();

            $table->index(['exercicio', 'status']);
        });

        Schema::create('programa_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('acao', 40);
            $table->json('alteracoes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programa_historicos');
        Schema::dropIfExists('programas');
    }
};
