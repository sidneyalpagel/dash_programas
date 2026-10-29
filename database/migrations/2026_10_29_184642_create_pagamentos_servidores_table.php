<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pagamentos de direitos e verbas a servidores (levantamento do RH).
 * Ficam fora dos programas municipais: não somam no total dos programas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos_servidores', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('exercicio');
            $table->string('verba');
            $table->text('descricao')->nullable();
            $table->string('base_legal')->nullable();
            $table->unsignedInteger('qtd_servidores')->nullable();
            $table->decimal('valor', 15, 2);
            $table->string('periodo', 120)->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('publicado')->default(true);
            $table->timestamps();

            $table->unique(['exercicio', 'verba']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos_servidores');
    }
};
