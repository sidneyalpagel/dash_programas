<?php

namespace Database\Seeders;

use App\Models\PagamentoServidor;
use Illuminate\Database\Seeder;

/**
 * Carga inicial do "Levantamento sintético de pagamento do RH" (2025 e 2026).
 * As descrições são explicações gerais; o RH pode ajustá-las e incluir a base legal pelo painel.
 */
class PagamentoServidorSeeder extends Seeder
{
    public function run(): void
    {
        $pagamentos = [
            [
                'exercicio' => 2025,
                'verba' => 'Licença-prêmio',
                'descricao' => 'Valor pago aos servidores referente à licença-prêmio, direito adquirido após um período de serviço prestado ao Município.',
                'qtd_servidores' => 63,
                'valor' => 1521715.50,
            ],
            [
                'exercicio' => 2025,
                'verba' => 'PDV – Programa de Demissão Voluntária',
                'descricao' => 'Indenização paga aos servidores que aderiram ao Programa de Demissão Voluntária e deixaram o serviço público municipal.',
                'qtd_servidores' => 31,
                'valor' => 5304285.81,
            ],
            [
                'exercicio' => 2025,
                'verba' => 'Quinquênio retroativo',
                'descricao' => 'Adicional por tempo de serviço, devido a cada cinco anos, pago de forma retroativa: são valores referentes a anos anteriores, quitados em 2025.',
                'qtd_servidores' => 298,
                'valor' => 987171.09,
            ],
            [
                'exercicio' => 2026,
                'verba' => 'Horas extras excedentes do banco de horas',
                'descricao' => 'Pagamento das horas extras que ultrapassaram o limite do banco de horas no fechamento do período.',
                'qtd_servidores' => 170,
                'valor' => 624644.43,
            ],
        ];

        foreach ($pagamentos as $ordem => $dados) {
            PagamentoServidor::updateOrCreate(
                ['exercicio' => $dados['exercicio'], 'verba' => $dados['verba']],
                $dados + ['ordem' => $ordem + 1],
            );
        }
    }
}
