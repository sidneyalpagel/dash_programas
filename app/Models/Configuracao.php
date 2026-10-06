<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Configurações simples do sistema (chave → valor), com cache. */
#[Fillable(['chave', 'valor'])]
class Configuracao extends Model
{
    public const EXERCICIO_EXIBICAO = 'exercicio_exibicao';

    protected $table = 'configuracoes';

    protected $primaryKey = 'chave';

    protected $keyType = 'string';

    public $incrementing = false;

    public static function obter(string $chave, mixed $padrao = null): mixed
    {
        return Cache::rememberForever("configuracao.{$chave}", fn () => static::find($chave)?->valor) ?? $padrao;
    }

    public static function definir(string $chave, mixed $valor): void
    {
        static::updateOrCreate(['chave' => $chave], ['valor' => $valor]);
        Cache::forget("configuracao.{$chave}");
    }
}
