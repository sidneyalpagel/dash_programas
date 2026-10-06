<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Rascunho: só o painel vê (revisado pela pré-visualização). Publicado: está no site. */
enum StatusPrograma: string implements HasLabel, HasColor
{
    case Rascunho = 'rascunho';
    case Publicado = 'publicado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::Publicado => 'Publicado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Rascunho => 'gray',
            self::Publicado => 'success',
        };
    }
}
