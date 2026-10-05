<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FonteRecurso: string implements HasLabel
{
    case Municipal = 'municipal';
    case Estadual = 'estadual';
    case Federal = 'federal';
    case Convenio = 'convenio';
    case NaoInformado = 'nao_informado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Municipal => 'Recurso do Município',
            self::Estadual => 'Recurso do Estado',
            self::Federal => 'Recurso da União',
            self::Convenio => 'Convênio (mais de uma esfera)',
            self::NaoInformado => 'Não informado',
        };
    }
}
