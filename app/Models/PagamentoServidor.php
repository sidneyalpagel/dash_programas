<?php

namespace App\Models;

use Database\Factories\PagamentoServidorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Verba paga a servidores municipais num exercício (licença-prêmio, PDV,
 * quinquênio, horas extras…). Não é programa municipal e não entra nos totais do site.
 */
#[Fillable(['exercicio', 'verba', 'descricao', 'base_legal', 'qtd_servidores', 'valor', 'periodo', 'ordem', 'publicado'])]
class PagamentoServidor extends Model
{
    /** @use HasFactory<PagamentoServidorFactory> */
    use HasFactory;

    protected $table = 'pagamentos_servidores';

    protected function casts(): array
    {
        return [
            'exercicio' => 'integer',
            'qtd_servidores' => 'integer',
            'valor' => 'decimal:2',
            'ordem' => 'integer',
            'publicado' => 'boolean',
        ];
    }

    public function scopePublicados(Builder $query): Builder
    {
        return $query->where('publicado', true);
    }
}
