<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'nome_curto', 'slug', 'cor', 'apresentacao', 'endereco', 'telefone', 'email', 'ordem'])]
class Secretaria extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function programas(): HasMany
    {
        return $this->hasMany(Programa::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('ordem', fn (Builder $query) => $query->orderBy('ordem')->orderBy('nome'));
    }
}
