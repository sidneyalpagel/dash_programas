<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['programa_id', 'user_id', 'acao', 'alteracoes'])]
class ProgramaHistorico extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'alteracoes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
