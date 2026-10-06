<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * Como o recurso chega ao cidadão. Classificação usada na nota explicativa
 * do demonstrativo consolidado.
 */
enum Mecanismo: string implements HasColor, HasDescription, HasLabel
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
            self::IncentivoProdutivo => 'Incentivos a produtores, empresas e entidades',
            self::ServicoPublico => 'Serviços e ações públicas',
        };
    }

    public function rotuloCurto(): string
    {
        return match ($this) {
            self::TransferenciaRenda => 'Benefícios a famílias',
            self::BolsaAuxilio => 'Bolsas e auxílios',
            self::IncentivoProdutivo => 'Incentivos financeiros',
            self::ServicoPublico => 'Serviços públicos',
        };
    }

    /**
     * Explicação do tipo como um todo, com exemplos de vários programas.
     * Usada nas páginas que mostram o conjunto (início e "Entenda").
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::TransferenciaRenda => 'A Prefeitura concede um benefício a famílias que precisam de apoio: em dinheiro ou custeando despesas como moradia, energia, gás e funeral.',
            self::BolsaAuxilio => 'Valor concedido a uma pessoa específica, como um estudante, um atleta ou uma pessoa com TEA, para ajudar em uma atividade ou atendimento.',
            self::IncentivoProdutivo => 'A Prefeitura reembolsa juros, subsidia custos ou repassa recursos para estimular uma atividade: a produção rural, as empresas locais e as entidades esportivas.',
            self::ServicoPublico => 'A Prefeitura usa o recurso para executar o programa: presta o serviço ou compra e contrata o que ele oferece, como transporte, merenda, máquinas, cursos e eventos.',
        };
    }

    /**
     * Explicação sem exemplos, válida para qualquer programa do tipo.
     * Usada na ficha, onde exemplos de outros programas não fazem sentido.
     */
    public function explicacaoNaFicha(): string
    {
        return match ($this) {
            self::TransferenciaRenda => 'O benefício é concedido diretamente à família atendida, em dinheiro ou custeando uma despesa dela.',
            self::BolsaAuxilio => 'O valor é concedido individualmente a cada pessoa atendida, para ajudar em uma atividade ou atendimento.',
            self::IncentivoProdutivo => 'A Prefeitura repassa recursos, reembolsa custos ou subsidia juros de quem desenvolve a atividade que o programa quer estimular.',
            self::ServicoPublico => 'Não é um benefício em dinheiro: a Prefeitura usa o recurso para executar o programa, prestando o serviço ou comprando e contratando o que ele oferece.',
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
