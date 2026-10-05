<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusPrograma: string implements HasLabel, HasColor
{
    case Rascunho = 'rascunho';
    case EmRevisao = 'em_revisao';
    case Publicado = 'publicado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::EmRevisao => 'Aguardando revisão',
            self::Publicado => 'Publicado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Rascunho => 'gray',
            self::EmRevisao => 'warning',
            self::Publicado => 'success',
        };
    }
}
