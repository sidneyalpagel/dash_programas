<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum TipoValor: string implements HasLabel, HasDescription
{
    case Anual = 'anual';
    case Anualizado = 'anualizado';
    case SemCusto = 'sem_custo';

    public function getLabel(): string
    {
        return match ($this) {
            self::Anual => 'Valor do ano',
            self::Anualizado => 'Valor anualizado',
            self::SemCusto => 'Sem custo direto',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Anual => 'Quanto o programa custa no exercício.',
            self::Anualizado => 'Programa de vários anos: informe o valor total e a vigência; o sistema divide pelo número de anos.',
            self::SemCusto => 'Não há gasto direto do Município (ex.: crédito estadual, atendimento feito pela equipe).',
        };
    }
}
