<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretarias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('nome_curto', 60);
            $table->string('slug')->unique();
            $table->string('cor', 7)->default('#1d3a6b');
            $table->text('apresentacao')->nullable();
            $table->string('endereco')->nullable();
            $table->string('telefone', 40)->nullable();
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('secretaria_id')->nullable()->after('email')->constrained()->nullOnDelete();
            $table->boolean('is_admin')->default(false)->after('secretaria_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('secretaria_id');
            $table->dropColumn('is_admin');
        });

        Schema::dropIfExists('secretarias');
    }
};
