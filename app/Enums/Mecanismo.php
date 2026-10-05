<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * Como o recurso chega ao cidadão. Classificação usada na nota explicativa
 * do demonstrativo consolidado.
 */
enum Mecanismo: string implements HasLabel, HasDescription, HasColor
{
    case TransferenciaRenda = 'transferencia_renda';
    case BolsaAuxilio = 'bolsa_auxilio';
    case IncentivoProdutivo = 'incentivo_produtivo';
    case ServicoPublico = 'servico_publico';

    public function getLabel(): string
    {
        return match ($this) {
            self::TransferenciaRenda => 'Transferência de renda e benefícios sociais',
            self::BolsaAuxilio => 'Bolsas e auxílios individuais',
            self::IncentivoProdutivo => 'Incentivos a produtores e empresas',
            self::ServicoPublico => 'Serviços e ações públicas',
        };
    }

    public function rotuloCurto(): string
    {
        return match ($this) {
            self::TransferenciaRenda => 'Benefícios a famílias',
            self::BolsaAuxilio => 'Bolsas e auxílios',
            self::IncentivoProdutivo => 'Incentivos à produção',
            self::ServicoPublico => 'Serviços públicos',
        };
    }

    /** Explicação em linguagem simples, exibida no site público. */
    public function getDescription(): string
    {
        return match ($this) {
            self::TransferenciaRenda => 'A Prefeitura paga um benefício em dinheiro, ou custeia moradia e energia, para famílias que precisam de apoio.',
            self::BolsaAuxilio => 'Valor pago a uma pessoa específica, como um estudante ou um atleta, para ajudar em uma atividade.',
            self::IncentivoProdutivo => 'A Prefeitura reembolsa juros ou custos de quem produz e gera emprego: produtores rurais, empresas e entidades esportivas.',
            self::ServicoPublico => 'A Prefeitura presta o serviço diretamente: transporte, merenda, máquinas, cursos, eventos e atendimento.',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TransferenciaRenda => 'success',
            self::BolsaAuxilio => 'warning',
            self::IncentivoProdutivo => 'info',
            self::ServicoPublico => 'gray',
        };
    }

    /** Variável CSS usada nos gráficos do site público. */
    public function corCss(): string
    {
        return 'var(--mec-'.$this->value.')';
    }
}
