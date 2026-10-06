<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A própria secretaria revisa (rascunho + pré-visualização) e publica:
 * sai a etapa "aguardando revisão" e as propostas de alteração.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('programas')->where('status', 'em_revisao')->update(['status' => 'rascunho']);

        Schema::table('programas', function (Blueprint $table) {
            $table->dropColumn('alteracoes_pendentes');
        });
    }

    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            $table->json('alteracoes_pendentes')->nullable()->after('status');
        });
    }
};
