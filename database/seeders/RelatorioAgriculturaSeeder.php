<?php

namespace Database\Seeders;

use App\Enums\FonteRecurso;
use App\Enums\Mecanismo;
use App\Enums\StatusPrograma;
use App\Enums\TipoValor;
use App\Models\Programa;
use App\Models\Secretaria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Relatórios demonstrativos 2025 e 2026 da Secretaria de Agricultura e Abastecimento Rural.
 *
 * - 2025: os números do relatório substituem os do PDF nos programas já publicados.
 * - Ações que o PDF não trazia (2025) e todo o exercício 2026 entram como rascunho, com as
 *   ressalvas do relatório na observação interna, para a Secretaria conferir e publicar.
 *
 * Rascunhos que já existem não são tocados: rodar de novo não desfaz o que a Secretaria editou.
 */
class RelatorioAgriculturaSeeder extends Seeder
{
    private const MAQUINAS = 'Máquinas e veículos da Secretaria';

    private const AGUA = 'Abastecimento de água no interior';

    private int $secretariaId;

    public function run(): void
    {
        $secretariaId = Secretaria::where('slug', 'agricultura')->value('id');

        if (! $secretariaId) {
            return;
        }

        $this->secretariaId = $secretariaId;

        DB::transaction(function () {
            foreach ($this->atualizacoes2025() as $slug => $dados) {
                $programa = $this->programa(2025, $slug);
                $programa->fill($dados);
                $programa->acaoHistorico = 'atualizado pelo relatório 2025';
                $programa->save();
            }

            foreach ($this->novos2025() as $dados) {
                $this->criarRascunho(2025, $dados);
            }

            foreach ($this->continuados2026() as $slug => $dados) {
                $copia = $this->programa(2025, $slug)->copiarPara(2026);

                if ($copia) {
                    $copia->fill($dados);
                    $copia->acaoHistorico = 'preenchido pelo relatório 2026';
                    $copia->save();
                }
            }

            foreach ($this->novos2026() as $dados) {
                $this->criarRascunho(2026, $dados);
            }
        });
    }

    private function programa(int $exercicio, string $slug): Programa
    {
        return Programa::where('secretaria_id', $this->secretariaId)
            ->where('exercicio', $exercicio)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /** @param  array<string, mixed>  $dados */
    private function criarRascunho(int $exercicio, array $dados): void
    {
        $existe = Programa::where('secretaria_id', $this->secretariaId)
            ->where('exercicio', $exercicio)
            ->where(fn ($q) => $q->where('nome', $dados['nome'])->orWhere('slug', Str::slug($dados['nome'])))
            ->exists();

        if ($existe) {
            return;
        }

        $programa = new Programa($dados + [
            'secretaria_id' => $this->secretariaId,
            'exercicio' => $exercicio,
            'mecanismo' => Mecanismo::ServicoPublico,
            'publico_alvo' => ['produtores_rurais'],
            'fonte_recurso' => FonteRecurso::Municipal,
            'tipo_valor' => TipoValor::Anual,
            'status' => StatusPrograma::Rascunho,
        ]);
        $programa->acaoHistorico = "importado do relatório {$exercicio}";
        $programa->save();
    }

    /**
     * @param  list<array{0: string, 1: string, 2: int}>  $leis  [tipo, número, ano]
     * @return list<array{tipo: string, numero: string, ano: int, link: null}>
     */
    private function leis(array $leis): array
    {
        return array_map(fn (array $lei) => ['tipo' => $lei[0], 'numero' => $lei[1], 'ano' => $lei[2], 'link' => null], $leis);
    }

    /**
     * Programas publicados de 2025, pelo slug: o relatório substitui os números do PDF.
     *
     * @return array<string, array<string, mixed>>
     */
    private function atualizacoes2025(): array
    {
        return [
            'servico-de-hora-maquina' => [
                'qtd_atendidos' => 123,
                'unidade_atendidos' => 'produtores',
                'detalhe_atendidos' => 'piscicultura: 64 · conservação do solo e pastagens: 19 · terraplanagem e esterqueiras: 40',
                'valor' => 3370943.00,
                'nota_publica' => 'Investimento aproximado: piscicultura R$ 725.000,00; conservação do solo e limpeza de pastagens R$ 118.700,00; terraplanagem e esterqueiras R$ 2.527.243,00.',
            ],
            'incentivo-para-pescadores' => [
                'descricao' => 'Reembolso de até R$ 3.500,00 por pescador profissional, a cada período de 2 anos, na compra de materiais e equipamentos para a atividade pesqueira.',
                'qtd_atendidos' => 39,
                'unidade_atendidos' => 'pescadores',
                'valor' => 136500.00,
            ],
            'incentivo-para-apicultores' => [
                'descricao' => 'Reembolso de até R$ 3.500,00 por apicultor, a cada período de 2 anos, na compra de equipamentos e utensílios para a atividade apícola.',
                'qtd_atendidos' => 31,
                'unidade_atendidos' => 'apicultores',
                'valor' => 108500.00,
            ],
            'aquisicao-de-alimentos-da-agricultura-familiar-paa' => [
                'nome' => 'Aquisição de Alimentos da Agricultura Familiar (PMAAF)',
                'qtd_atendidos' => 38,
                'unidade_atendidos' => 'produtores',
                'detalhe_atendidos' => 'e 1 associação com mais de 60 integrantes',
                'valor' => 1470000.00,
                'nota_publica' => 'Valor do período de setembro de 2025 a setembro de 2026.',
            ],
            'melhoramento-genetico-da-pecuaria-de-leite' => [
                'qtd_atendidos' => 283,
                'unidade_atendidos' => 'produtores',
                'detalhe_atendidos' => 'cerca de 5.800 inseminações',
                'valor' => 431056.00,
                'nota_publica' => 'Valores do período de agosto de 2024 a agosto de 2025.',
            ],
            'comercializacao-e-registro-de-produtos-de-origem-animal-simpoa' => [
                'qtd_atendidos' => 11,
                'unidade_atendidos' => 'estabelecimentos',
                'detalhe_atendidos' => 'certificados, além dos que estão em processo de certificação',
            ],
            // O relatório 2025 traz só a cama de aviário; o valor do PDF inclui o calcário e fica.
            'distribuicao-de-calcario-ou-cama-de-aviario' => [
                'observacao_interna' => 'Relatório 2025 da Secretaria: cama de aviário para 92 produtores, R$ 1.331.310,00 (Contrato nº 115/2025, Paloma T. da Cruz & Cia Ltda., 3.345 t a R$ 398,00/t). O relatório não traz o calcário de 2025; mantidos os números do PDF (444 atendidos, R$ 3.163.529,00), que somam os dois.',
            ],
            'apoio-a-comercializacao-direta' => [
                'bases_legais' => $this->leis([['Lei Municipal', '1.783', 2008], ['Lei Municipal', '2.644', 2018]]),
                'observacao_interna' => 'Relatório 2025 da Secretaria ("Feira municipal e apoio às associações"): cerca de 15 feirantes, sem valor. Mantidos os números do PDF.',
            ],
            'pavimentacao-poliedrica' => [
                'qtd_atendidos' => null,
                'valor' => null,
                'nota_publica' => 'A Secretaria ainda não informou os números de 2025. Em 2024, foram 40 produtores atendidos, com investimento de R$ 1.663.425,05.',
                'observacao_interna' => 'Relatório 2025 da Secretaria ("Pátios rurais"): o dado disponível (40 produtores, R$ 1.663.425,05) é de 2024 e não deve ser registrado como 2025.',
            ],
            'viabiliza-agro' => [
                'bases_legais' => $this->leis([['Lei Municipal', '2.943', 2022], ['Lei Municipal', '3.087', 2023]]),
                'ano_criacao' => 2022,
                'qtd_atendidos' => 669,
                'unidade_atendidos' => 'operações de crédito',
                'detalhe_atendidos' => 'contratadas de 2022 a 2024, somando cerca de R$ 110,6 milhões em crédito',
                'valor_total_vigencia' => 16900000.00,
                'vigencia_anos' => 8,
                'nota_publica' => 'Os subsídios das operações somam cerca de R$ 16,9 milhões, pagos ao longo de 8 anos. O valor mostrado é o total dividido por 8, para poder ser comparado com os demais programas, que são anuais.',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function novos2025(): array
    {
        return [
            [
                'nome' => 'Aquisição de Pá Carregadeira',
                'grupo' => self::MAQUINAS,
                'descricao' => 'Compra de 1 pá carregadeira nova para os serviços da Secretaria aos produtores rurais, com recursos do Convênio nº 92/2025 com a Secretaria de Estado das Cidades (Paranacidade).',
                'bases_legais' => $this->leis([['Convênio', '92', 2025]]),
                'fonte_recurso' => FonteRecurso::Estadual,
                'valor' => 960000.00,
                'observacao_interna' => 'Relatório 2025: Contrato nº 285/2025; Pregão Eletrônico nº 045/2025; contratada BRW Máquinas e Vendas de Equipamentos Ltda.',
            ],
            [
                'nome' => 'Aquisição de Retroescavadeira',
                'grupo' => self::MAQUINAS,
                'descricao' => 'Compra de 1 retroescavadeira para os serviços rurais do Município, com recursos de emenda parlamentar.',
                'fonte_recurso' => FonteRecurso::NaoInformado,
                'valor' => 400000.00,
                'nota_publica' => 'Valor contratado.',
                'observacao_interna' => 'Relatório 2025: Contrato nº 342/2025; Inexigibilidade nº 050/2025; Emenda Parlamentar nº 202140740001; contratada Veneza Equipamentos Sul Comércio Ltda. O relatório avisa que entrega e pagamento não estão confirmados: conferir antes de publicar. Informar se a emenda é federal ou estadual.',
            ],
            [
                'nome' => 'Aquisição de Escavadeira e Retroescavadeiras',
                'grupo' => self::MAQUINAS,
                'descricao' => 'Compra de 1 escavadeira hidráulica e 3 retroescavadeiras pelo Programa Estradas da Integração, com recursos do Convênio nº 640/2025 com a Secretaria de Estado da Agricultura e do Abastecimento (SEAB).',
                'bases_legais' => $this->leis([['Convênio', '640', 2025]]),
                'fonte_recurso' => FonteRecurso::Estadual,
                'valor' => 1879900.00,
                'observacao_interna' => 'Relatório 2025: Contrato nº 343/2025.',
            ],
            [
                'nome' => 'Aquisição de Caminhão-Pipa',
                'grupo' => self::MAQUINAS,
                'descricao' => 'Compra de caminhão-pipa com tanque de 16.000 litros para levar água potável às comunidades do interior.',
                'publico_alvo' => ['comunidades'],
                'valor' => 608000.00,
                'nota_publica' => 'Valor contratado.',
                'observacao_interna' => 'Relatório 2025: Contrato nº 083/2025; fornecedora Metalúrgica Perpétuo Socorro Ltda. O relatório menciona outra unidade, sem valor ou contrato confirmado. Confirmar a fonte no empenho.',
            ],
            [
                'nome' => 'Aquisição de Caminhão Munck',
                'grupo' => self::MAQUINAS,
                'descricao' => 'Compra de caminhão Munck para apoiar os serviços de abastecimento de água e de infraestrutura rural.',
                'valor' => 728500.00,
                'observacao_interna' => 'Relatório 2025: valor de referência/contratação. Confirmar contrato, pagamento e fonte antes de publicar.',
            ],
            [
                'nome' => 'Poços e Redes de Água em Santa Clara e Sanga Natal',
                'grupo' => self::AGUA,
                'descricao' => 'Implantação de poços, adutora e reservatório para o abastecimento de água nas comunidades Linha Santa Clara e Sanga Natal.',
                'bases_legais' => $this->leis([['Convênio', '4123501', 2023]]),
                'publico_alvo' => ['comunidades'],
                'fonte_recurso' => FonteRecurso::Convenio,
                'qtd_atendidos' => 55,
                'unidade_atendidos' => 'famílias',
                'detalhe_atendidos' => 'número aproximado',
                'valor' => 648500.00,
                'nota_publica' => 'Valor do contrato: Linha Santa Clara R$ 284.000,00 e Sanga Natal R$ 364.500,00. Obra com convênio com a Itaipu Binacional (programa Mais que Energia).',
                'observacao_interna' => 'Relatório 2025: Contrato nº 039/2025, Jullian L. Stulp e Cia Ltda. Convênio Itaipu nº 4123501/2023: R$ 510.000,00 da Itaipu + R$ 90.000,00 do Município = R$ 600.000,00. Diferença de R$ 48.500,00 para o contrato a esclarecer.',
            ],
            [
                'nome' => 'Melhoria do Abastecimento de Água em Outras Comunidades',
                'grupo' => self::AGUA,
                'descricao' => 'Obras de melhoria e expansão do abastecimento de água em Vila Celeste, São Roque, São Clemente e Esquina Céu Azul.',
                'publico_alvo' => ['comunidades'],
                'qtd_atendidos' => 4,
                'unidade_atendidos' => 'comunidades',
                'valor' => 1109735.80,
                'nota_publica' => 'Vila Celeste R$ 153.987,00; São Roque R$ 406.897,80; São Clemente R$ 231.351,00; Esquina Céu Azul R$ 317.500,00.',
                'observacao_interna' => 'Relatório 2025: valores de referência. Verificar se são contratados, empenhados, liquidados ou pagos antes de publicar; não somar subtotais que possam se sobrepor.',
            ],
            [
                'nome' => 'Melhorias na Captação, Tratamento e Distribuição de Água',
                'grupo' => self::AGUA,
                'descricao' => 'Melhorias na captação e no tratamento da água, troca de tubulações e compra de materiais e equipamentos para o abastecimento rural.',
                'publico_alvo' => ['comunidades'],
                'observacao_interna' => 'Relatório 2025, valores de referência: captação e tratamento R$ 900.000,00; tubulações R$ 400.000,00; materiais e equipamentos R$ 300.000,00. O relatório pede para não somar sem verificar sobreposição com as obras das comunidades.',
            ],
            ...$this->continuas(2025),
        ];
    }

    /**
     * Ações continuadas da Secretaria, iguais nos dois relatórios.
     *
     * @return list<array<string, mixed>>
     */
    private function continuas(int $exercicio): array
    {
        return [
            [
                'nome' => 'Assistência Técnica e Extensão Rural',
                'descricao' => 'Orientação e apoio técnico aos produtores em cadeias produtivas, agroindústria, boas práticas, planejamento e sustentabilidade.',
                'valor' => 178656.00,
                'nota_publica' => 'Valor anual aproximado.',
                'observacao_interna' => "Relatório {$exercicio}: referência aproximada anual; valor e número de atendimentos do ano não individualizados.",
            ],
            [
                'nome' => 'Manutenção de Máquinas e Implementos',
                'descricao' => 'Manutenção de distribuidores, grades, calcariadoras, escarificadores, terraceadores e outros implementos usados nos serviços aos produtores.',
                'valor' => 400000.00,
                'nota_publica' => 'Valor anual aproximado.',
                'observacao_interna' => "Relatório {$exercicio}: referência aproximada anual. É custeio da própria Secretaria: avaliar se deve aparecer como programa.",
            ],
        ];
    }

    /**
     * Programas de 2025 que seguem em 2026: copiados (rascunho) e preenchidos com o relatório.
     *
     * @return array<string, array<string, mixed>>
     */
    private function continuados2026(): array
    {
        return [
            'servico-de-hora-maquina' => [
                'qtd_atendidos' => 67,
                'unidade_atendidos' => 'produtores',
                'detalhe_atendidos' => 'piscicultura: 19 · conservação do solo e pastagens: 15 · terraplanagem e esterqueiras: 33',
                'valor' => 1605000.00,
                'nota_publica' => 'Investimento aproximado: piscicultura R$ 232.000,00; conservação do solo e limpeza de pastagens R$ 148.000,00; terraplanagem e esterqueiras R$ 1.225.000,00.',
            ],
            'incentivo-para-pescadores' => [
                'qtd_atendidos' => 11,
                'unidade_atendidos' => 'pescadores',
                'valor' => 38500.00,
            ],
            'incentivo-para-apicultores' => [
                'qtd_atendidos' => 17,
                'unidade_atendidos' => 'apicultores',
                'valor' => 59500.00,
            ],
            'aquisicao-de-alimentos-da-agricultura-familiar-paa' => [
                'qtd_atendidos' => 36,
                'unidade_atendidos' => 'produtores',
                'detalhe_atendidos' => 'e 1 associação com mais de 60 integrantes',
                'nota_publica' => null,
                'observacao_interna' => 'O relatório 2026 repete o R$ 1,47 milhão de set/2025 a set/2026, já lançado em 2025. Informar o valor de 2026 sem repetir esse período.',
            ],
            'melhoramento-genetico-da-pecuaria-de-leite' => [
                'qtd_atendidos' => 283,
                'unidade_atendidos' => 'produtores',
                'detalhe_atendidos' => 'cerca de 6.370 inseminações',
                'valor' => 497688.10,
                'nota_publica' => 'Valores do período de agosto de 2025 a agosto de 2026.',
            ],
            'comercializacao-e-registro-de-produtos-de-origem-animal-simpoa' => [
                'qtd_atendidos' => 11,
                'unidade_atendidos' => 'estabelecimentos',
                'detalhe_atendidos' => 'certificados, além dos que estão em processo de certificação',
                'valor' => 98000.00,
                'nota_publica' => 'Valor anual aproximado.',
            ],
            'distribuicao-de-calcario-ou-cama-de-aviario' => [
                'qtd_atendidos' => 359,
                'unidade_atendidos' => 'produtores',
                'detalhe_atendidos' => 'com calcário',
                'valor' => 1937150.00,
                'nota_publica' => 'Valor aproximado.',
                'observacao_interna' => 'O relatório 2026 traz só o calcário. Incluir a cama de aviário, se houver.',
            ],
            'apoio-a-comercializacao-direta' => [
                'qtd_atendidos' => 15,
                'unidade_atendidos' => 'feirantes',
                'detalhe_atendidos' => 'número aproximado',
                'observacao_interna' => 'O relatório 2026 ("Feira municipal e apoio às associações") não traz valor.',
            ],
            'desenvolve-agro' => [
                'qtd_atendidos' => 29,
                'unidade_atendidos' => 'produtores',
                'detalhe_atendidos' => '27 suinocultores e 2 avicultores; 50,4 mil m² de granjas',
                'valor_total_vigencia' => 7300000.00,
                'observacao_interna' => 'Relatório 2026: R$ 7,3 milhões de reembolso. Confirmar se o reembolso é pago em 10 anos, como em 2025.',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function novos2026(): array
    {
        return [
            [
                'nome' => 'Aquisição de Pás Carregadeiras',
                'grupo' => self::MAQUINAS,
                'descricao' => 'Compra de 2 pás carregadeiras pelo Programa Estradas da Integração, com recursos do Convênio nº 640/2025 com a Secretaria de Estado da Agricultura e do Abastecimento (SEAB).',
                'bases_legais' => $this->leis([['Convênio', '640', 2025]]),
                'fonte_recurso' => FonteRecurso::Estadual,
                'valor' => 1589800.00,
                'observacao_interna' => 'Relatório 2026: Contrato nº 031/2026, por adesão a ata de registro de preços; contratada Veneza Equipamentos Sul Comércio Ltda.',
            ],
            [
                'nome' => 'Estações de Abastecimento para Pulverizadores',
                'grupo' => self::AGUA,
                'descricao' => 'Implantação de estações comunitárias de abastecimento de água para pulverizadores agrícolas no interior do Município.',
                'valor' => 638100.00,
                'nota_publica' => 'Obras em andamento. Valor dos dois contratos: R$ 466.500,00 e R$ 171.600,00.',
                'observacao_interna' => 'Relatório 2026: Concorrência Eletrônica nº 010/2026; Contrato nº 143/2026 (O. Drescher) e Contrato nº 144/2026 (Edifice Construtora). O relatório fala em 10 estações, mas lista 11 localidades: Linha Vera Cruz e Linha São Vicente Chico (Vila Celeste); Esquina Bela Vista (Esquina Céu Azul); Linha Terol (Sub Sede); Linha Sanga Natal, Linha União e Vista Alta (São Clemente); Linha Cabeceira da Moreninha, Linha Santa Cruz e Linha Pacuri (Moreninha); Linha Santo Antônio (São Roque). Conferir a quantidade.',
            ],
            [
                'nome' => 'Fortalecimento da Pesca Artesanal',
                'descricao' => 'Compra de equipamentos e materiais de pesca e estrutura de apoio à Colônia de Pescadores Nossa Senhora dos Navegantes, para fortalecer a pesca artesanal.',
                'fonte_recurso' => FonteRecurso::Federal,
                'qtd_atendidos' => 60,
                'unidade_atendidos' => 'pescadores',
                'valor' => 181457.85,
                'observacao_interna' => 'Relatório 2026: Ação 2072; Plano de Ação nº 09032025-082613; vínculo 10161 (Emenda Individual Impositiva – Transferência Especial). Confirmar que a emenda é federal.',
            ],
            [
                'nome' => 'Testes de Brucelose e Tuberculose',
                'descricao' => 'Apoio sanitário à pecuária com a realização de testes de brucelose e tuberculose nos rebanhos.',
                'qtd_atendidos' => 105,
                'unidade_atendidos' => 'produtores',
                'detalhe_atendidos' => '2.124 testes',
                'valor' => 63720.00,
                'observacao_interna' => 'Relatório 2026: base legal específica não indicada.',
            ],
            ...$this->continuas(2026),
        ];
    }
}
