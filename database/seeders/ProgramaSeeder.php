<?php

namespace Database\Seeders;

use App\Enums\FonteRecurso;
use App\Enums\Mecanismo;
use App\Enums\StatusPrograma;
use App\Enums\TipoValor;
use App\Models\Programa;
use App\Models\Secretaria;
use Illuminate\Database\Seeder;

/**
 * Carga inicial a partir do "Demonstrativo Consolidado de Programas Municipais
 * em Execução 2025-2026" (docs/consolidacao_programas_2025.pdf).
 *
 * Campos que o PDF não traz ficam em branco e aparecem como pendência no painel.
 */
class ProgramaSeeder extends Seeder
{
    private const EXERCICIO = 2025;

    public function run(): void
    {
        $secretarias = Secretaria::pluck('id', 'slug');

        foreach ($this->programas() as $slugSecretaria => $programas) {
            foreach ($programas as $dados) {
                $dados['bases_legais'] = array_map(
                    fn (array $lei) => $lei + ['tipo' => 'Lei Municipal', 'link' => null],
                    $dados['bases_legais'] ?? [],
                );

                $programa = Programa::firstOrNew([
                    'secretaria_id' => $secretarias[$slugSecretaria],
                    'exercicio' => self::EXERCICIO,
                    'nome' => $dados['nome'],
                ]);

                $programa->fill($dados + [
                    'fonte_recurso' => FonteRecurso::Municipal,
                    'tipo_valor' => TipoValor::Anual,
                    'status' => StatusPrograma::Publicado,
                    'publicado_em' => now(),
                ]);
                $programa->acaoHistorico = $programa->exists ? null : 'importado do PDF';
                $programa->save();
            }
        }
    }

    private function programas(): array
    {
        $produtores = ['produtores_rurais'];

        return [
            'agricultura' => [
                [
                    'nome' => 'Desenvolve Agro',
                    'bases_legais' => [['numero' => '3.339', 'ano' => 2025], ['numero' => '3.354', 'ano' => 2025]],
                    'ano_criacao' => 2025,
                    'descricao' => 'Incentivo financeiro, em regime de reembolso, para implantação de novos empreendimentos de suinocultura e avicultura de corte, subsidiando juros de operações de crédito para construção de granjas.',
                    'mecanismo' => Mecanismo::IncentivoProdutivo,
                    'publico_alvo' => $produtores,
                    'qtd_atendidos' => 25,
                    'unidade_atendidos' => 'atendidos',
                    'tipo_valor' => TipoValor::Anualizado,
                    'valor_total_vigencia' => 6311380.50,
                    'vigencia_anos' => 10,
                    'nota_publica' => 'Programa com vigência de 10 anos. O valor mostrado é o total dividido por 10, para poder ser comparado com os demais programas, que são anuais.',
                ],
                [
                    'nome' => 'Distribuição de Calcário ou Cama de Aviário',
                    'bases_legais' => [['numero' => '2.690', 'ano' => 2018]],
                    'ano_criacao' => 2018,
                    'descricao' => 'Distribuição de calcário ou cama de aviário para correção e melhoria da qualidade do solo.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => $produtores,
                    'qtd_atendidos' => 444,
                    'unidade_atendidos' => 'atendidos',
                    'valor' => 3163529.00,
                ],
                [
                    'nome' => 'Comercialização e Registro de Produtos de Origem Animal (SIM/POA)',
                    'bases_legais' => [['numero' => '2.673', 'ano' => 2018], ['numero' => '3.244', 'ano' => 2024]],
                    'ano_criacao' => 2018,
                    'descricao' => 'Garantia da segurança alimentar e da qualidade sanitária dos produtos de origem animal produzidos no Município.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['produtores_rurais', 'empresas'],
                    'qtd_atendidos' => 10,
                    'unidade_atendidos' => 'atendidos',
                    'valor' => 98684.00,
                ],
                [
                    'nome' => 'Apoio à Comercialização Direta',
                    'bases_legais' => [['numero' => '1.783', 'ano' => 2008]],
                    'ano_criacao' => 2008,
                    'descricao' => 'Apoio a iniciativas de comercialização direta da agricultura familiar, produtores de orgânicos, culinária artesanal e artesanato.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['produtores_rurais', 'artistas'],
                    'qtd_atendidos' => 16,
                    'unidade_atendidos' => 'atendidos',
                    'valor' => 60000.00,
                ],
                [
                    'nome' => 'Pavimentação Poliédrica',
                    'bases_legais' => [['numero' => '3.086', 'ano' => 2023]],
                    'ano_criacao' => 2023,
                    'descricao' => 'Melhoria dos pátios de propriedades rurais com pavimentação poliédrica, com subsídio público de 100% para áreas de até 1.000 m² por unidade produtiva.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => $produtores,
                    'qtd_atendidos' => 40,
                    'unidade_atendidos' => 'atendidos',
                    'valor' => 2343675.05,
                ],
                [
                    'nome' => 'Melhoramento Genético da Pecuária de Leite',
                    'bases_legais' => [['numero' => '2.538', 'ano' => 2017], ['numero' => '3.402', 'ano' => 2026]],
                    'ano_criacao' => 2017,
                    'descricao' => 'Fomento à produção leiteira por meio de subsídio para inseminação artificial em bovinos leiteiros e apoio às ações de sanidade animal.',
                    'mecanismo' => Mecanismo::IncentivoProdutivo,
                    'publico_alvo' => $produtores,
                    'qtd_atendidos' => 370,
                    'unidade_atendidos' => 'atendidos',
                    'valor' => 522402.09,
                ],
                [
                    'nome' => 'Aquisição de Alimentos da Agricultura Familiar (PAA)',
                    'bases_legais' => [['numero' => '2.939', 'ano' => 2022], ['numero' => '3.387', 'ano' => 2025]],
                    'ano_criacao' => 2022,
                    'descricao' => 'Aquisição institucional de alimentos produzidos pela agricultura familiar, com parâmetro no Programa de Aquisição de Alimentos.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => $produtores,
                    'qtd_atendidos' => 41,
                    'unidade_atendidos' => 'atendidos',
                    'valor' => 1753280.43,
                ],
                [
                    'nome' => 'Serviço de Hora Máquina',
                    'bases_legais' => [['numero' => '3.085', 'ano' => 2023]],
                    'ano_criacao' => 2023,
                    'descricao' => 'Terraplanagem e aterro, serviços diversos de hora-máquina, esterqueiras/biodigestores, lagoas de decantação, tanques para irrigação, cisternas e piscicultura.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => $produtores,
                    'qtd_atendidos' => 815,
                    'unidade_atendidos' => 'atendidos',
                    'valor' => 5721923.60,
                ],
                [
                    'nome' => 'Incentivo para Apicultores',
                    'bases_legais' => [['numero' => '3.028', 'ano' => 2022]],
                    'ano_criacao' => 2022,
                    'descricao' => 'Reembolso de até R$ 3.500,00 por produtor, a cada período de 2 anos.',
                    'mecanismo' => Mecanismo::IncentivoProdutivo,
                    'publico_alvo' => $produtores,
                    'qtd_atendidos' => 48,
                    'unidade_atendidos' => 'produtores',
                    'valor' => 168000.00,
                ],
                [
                    'nome' => 'Incentivo para Pescadores',
                    'bases_legais' => [['numero' => '2.991', 'ano' => 2022]],
                    'ano_criacao' => 2022,
                    'descricao' => 'Reembolso de até R$ 3.500,00 por produtor, a cada período de 2 anos.',
                    'mecanismo' => Mecanismo::IncentivoProdutivo,
                    'publico_alvo' => $produtores,
                    'qtd_atendidos' => 50,
                    'unidade_atendidos' => 'produtores',
                    'valor' => 175000.00,
                ],
                [
                    'nome' => 'Viabiliza Agro',
                    'bases_legais' => [['numero' => '3.339', 'ano' => 2025]],
                    'ano_criacao' => 2025,
                    'descricao' => 'Subsídio de juros ordinários do Plano Safra - PRONAF, limitado a 7% ao ano e a R$ 250.000,00 por operação.',
                    'mecanismo' => Mecanismo::IncentivoProdutivo,
                    'publico_alvo' => $produtores,
                    'qtd_atendidos' => 623,
                    'unidade_atendidos' => 'atendidos',
                    'tipo_valor' => TipoValor::Anualizado,
                    'valor_total_vigencia' => 11130676.48,
                    'vigencia_anos' => 8,
                    'nota_publica' => 'Programa com vigência de 8 anos. O valor mostrado é o total dividido por 8, para poder ser comparado com os demais programas, que são anuais.',
                ],
            ],

            'desenvolvimento-economico' => [
                [
                    'nome' => 'Desenvolve Santa Helena',
                    'descricao' => 'Programa de incentivo ao desenvolvimento econômico local. O valor corresponde ao total de juros repassados às empresas beneficiadas.',
                    'mecanismo' => Mecanismo::IncentivoProdutivo,
                    'publico_alvo' => ['empresas'],
                    'qtd_atendidos' => 160,
                    'unidade_atendidos' => 'empresas',
                    'valor' => 1832464.53,
                ],
                [
                    'nome' => 'Qualifica Santa Helena',
                    'descricao' => 'Cursos profissionalizantes de eletricista veicular e ar-condicionado automotivo, sem custo ao participante.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['trabalhadores'],
                    'qtd_atendidos' => 40,
                    'unidade_atendidos' => 'pessoas',
                    'detalhe_atendidos' => '2 turmas',
                    'tipo_valor' => TipoValor::SemCusto,
                ],
                [
                    'nome' => 'Fomento Paraná - Micro Emergência',
                    'grupo' => 'Fomento Paraná',
                    'descricao' => 'Linha de microcrédito emergencial do Governo do Estado, sem custo à Prefeitura.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['empresas'],
                    'fonte_recurso' => FonteRecurso::Estadual,
                    'qtd_atendidos' => 11,
                    'unidade_atendidos' => 'créditos',
                    'tipo_valor' => TipoValor::SemCusto,
                ],
                [
                    'nome' => 'Fomento Paraná - Micro Fácil',
                    'grupo' => 'Fomento Paraná',
                    'descricao' => 'Linha de microcrédito facilitado do Governo do Estado, sem custo à Prefeitura.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['empresas'],
                    'fonte_recurso' => FonteRecurso::Estadual,
                    'qtd_atendidos' => 5,
                    'unidade_atendidos' => 'créditos',
                    'tipo_valor' => TipoValor::SemCusto,
                ],
                [
                    'nome' => 'Fomento Paraná - Micro Mulher',
                    'grupo' => 'Fomento Paraná',
                    'descricao' => 'Linha de microcrédito do Governo do Estado voltada ao empreendedorismo feminino, sem custo à Prefeitura.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['empresas', 'mulheres'],
                    'fonte_recurso' => FonteRecurso::Estadual,
                    'qtd_atendidos' => 8,
                    'unidade_atendidos' => 'créditos',
                    'tipo_valor' => TipoValor::SemCusto,
                ],
                [
                    'nome' => 'Sala do Empreendedor',
                    'descricao' => 'Atendimento e orientação a micro e pequenos empresários, sem custo às empresas.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['empresas'],
                    'qtd_atendidos' => 657,
                    'unidade_atendidos' => 'empresas',
                    'tipo_valor' => TipoValor::SemCusto,
                ],
            ],

            'esportes' => [
                [
                    'nome' => 'Programa Bolsa Atleta Municipal',
                    'descricao' => 'Incentivo, valorização e apoio a atletas com desempenho esportivo destacado, para manutenção de treinamentos, aquisição de materiais, deslocamentos e participação em competições oficiais.',
                    'mecanismo' => Mecanismo::BolsaAuxilio,
                    'publico_alvo' => ['atletas'],
                    'qtd_atendidos' => 287,
                    'unidade_atendidos' => 'atletas',
                    'detalhe_atendidos' => 'limite de 360 vagas',
                    'valor' => 950000.00,
                    'observacao_interna' => 'O PDF cita apenas "Legislação Municipal Específica": informar número e ano da lei.',
                ],
                [
                    'nome' => 'Programa Municipal de Fomento ao Esporte',
                    'descricao' => 'Apoio financeiro e institucional a entidades esportivas sem fins lucrativos, para execução de projetos, formação de atletas e participação em competições oficiais.',
                    'mecanismo' => Mecanismo::IncentivoProdutivo,
                    'publico_alvo' => ['atletas', 'comunidades'],
                    'qtd_atendidos' => 7,
                    'unidade_atendidos' => 'associações',
                    'detalhe_atendidos' => 'cerca de 140 atletas',
                    'valor' => 1030000.00,
                    'observacao_interna' => 'O PDF cita "Parcerias com Entidades Esportivas" como base legal: informar a lei ou os termos de fomento.',
                ],
            ],

            'assistencia-social' => [
                [
                    'nome' => 'Programa Idosos que Fazem a Diferença',
                    'descricao' => 'Oficinas, bandas, datas comemorativas, almoços, intercâmbios e viagens.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['idosos'],
                    'qtd_atendidos' => 2000,
                    'unidade_atendidos' => 'idosos',
                    'valor' => 447799.69,
                ],
                [
                    'nome' => 'Programa Mulher Santa-Helenense Transformando o Presente',
                    'descricao' => 'Dia da Mulher, evento de integração, cursos e passeios/viagens.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['mulheres'],
                    'qtd_atendidos' => 630,
                    'unidade_atendidos' => 'mulheres',
                    'detalhe_atendidos' => '28 clubes de mães',
                    'valor' => 376731.70,
                ],
                [
                    'nome' => 'Assuntos Comunitários - Tendas',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['comunidades'],
                    'qtd_atendidos' => 1,
                    'unidade_atendidos' => 'comunidade',
                    'valor' => 8760.00,
                ],
                [
                    'nome' => 'Habita Santa Helena - Habitação de Interesse Social',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias'],
                    'qtd_atendidos' => 120,
                    'unidade_atendidos' => 'famílias',
                    'detalhe_atendidos' => 'em situação de vulnerabilidade social',
                    'valor' => 7580000.00,
                ],
                [
                    'nome' => 'Benefício Mãos à Obra',
                    'grupo' => 'Renda Santa Helena',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias'],
                    'qtd_atendidos' => 58,
                    'unidade_atendidos' => 'famílias',
                    'detalhe_atendidos' => 'em situação de vulnerabilidade social',
                    'valor' => 1100000.00,
                ],
                [
                    'nome' => 'Programa Energia Sustentável',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias'],
                    'qtd_atendidos' => 307,
                    'unidade_atendidos' => 'famílias',
                    'valor' => 3684000.00,
                ],
                [
                    'nome' => 'Serviço de Acolhimento em Família Acolhedora',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['criancas_adolescentes'],
                    'qtd_atendidos' => 36,
                    'unidade_atendidos' => 'crianças e adolescentes',
                    'valor' => 572893.00,
                ],
                [
                    'nome' => 'Acolhimento Familiar - Morada Fraterna',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['idosos'],
                    'qtd_atendidos' => 7,
                    'unidade_atendidos' => 'idosos',
                    'valor' => 186000.00,
                ],
                [
                    'nome' => 'Auxílio Funeral',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias'],
                    'qtd_beneficios' => 86,
                    'detalhe_atendidos' => '61 benefícios de R$ 3.000,00 e 22 de R$ 1.000,00',
                    'valor' => 205000.00,
                    'observacao_interna' => 'Conferir: 61 + 22 = 83 benefícios, mas o total informado no PDF é 86.',
                ],
                [
                    'nome' => 'Auxílio Translado Funeral',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias'],
                    'qtd_atendidos' => 14,
                    'unidade_atendidos' => 'pessoas',
                    'valor' => 19545.68,
                ],
                [
                    'nome' => 'Progredir',
                    'grupo' => 'Renda Santa Helena',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias'],
                    'qtd_beneficios' => 1139,
                    'valor' => 417659.91,
                ],
                [
                    'nome' => 'Gerar - Parcela 01',
                    'grupo' => 'Renda Santa Helena',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias'],
                    'qtd_beneficios' => 23,
                    'valor' => 3614.45,
                    'observacao_interna' => 'Parcelas 01 e 02 do Gerar estão como dois programas, como no PDF. Avaliar unir em um único cadastro.',
                ],
                [
                    'nome' => 'Gerar - Parcela 02',
                    'grupo' => 'Renda Santa Helena',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias'],
                    'qtd_beneficios' => 18,
                    'valor' => 2828.70,
                    'observacao_interna' => 'Parcelas 01 e 02 do Gerar estão como dois programas, como no PDF. Avaliar unir em um único cadastro.',
                ],
                [
                    'nome' => 'Auxílio Gás de Cozinha',
                    'grupo' => 'Renda Santa Helena',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias'],
                    'qtd_beneficios' => 47,
                    'valor' => 6401.40,
                ],
                [
                    'nome' => 'Engaja Jovem',
                    'grupo' => 'Renda Santa Helena',
                    'mecanismo' => Mecanismo::TransferenciaRenda,
                    'publico_alvo' => ['familias', 'criancas_adolescentes'],
                    'qtd_atendidos' => 9,
                    'unidade_atendidos' => 'adolescentes',
                    'qtd_beneficios' => 47,
                    'valor' => 9848.38,
                ],
            ],

            'educacao' => [
                [
                    'nome' => 'Educa Mais Santa Helena',
                    'bases_legais' => [['numero' => '3.120', 'ano' => 2023]],
                    'ano_criacao' => 2023,
                    'descricao' => 'Política pública de fomento à educação, com concessão de bolsas a estudantes do Ensino Fundamental para participação em atividades esportivas, culturais, recreativas, educacionais e socioemocionais complementares.',
                    'mecanismo' => Mecanismo::BolsaAuxilio,
                    'publico_alvo' => ['estudantes', 'criancas_adolescentes'],
                    'qtd_atendidos' => 1321,
                    'unidade_atendidos' => 'estudantes',
                    'valor' => 3187490.61,
                ],
                [
                    'nome' => 'Atendimento Especializado à Pessoa com TEA',
                    'bases_legais' => [['numero' => '3.161', 'ano' => 2023]],
                    'ano_criacao' => 2023,
                    'descricao' => 'Auxílio financeiro complementar aos serviços das Secretarias de Saúde e de Educação para atendimento multiprofissional especializado a pessoas com Transtorno do Espectro Autista.',
                    'mecanismo' => Mecanismo::BolsaAuxilio,
                    'publico_alvo' => ['pessoas_com_deficiencia', 'estudantes'],
                    'qtd_atendidos' => 137,
                    'unidade_atendidos' => 'estudantes',
                    'valor' => 857693.00,
                ],
                [
                    'nome' => 'Distribuição Gratuita de Uniformes Escolares',
                    'bases_legais' => [['numero' => '2.760', 'ano' => 2019]],
                    'ano_criacao' => 2019,
                    'descricao' => 'Fornecimento de kits de uniformes aos estudantes da rede pública municipal; para a rede estadual, restrito a estudantes em vulnerabilidade social inscritos no CadÚnico.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['estudantes', 'criancas_adolescentes'],
                    'qtd_atendidos' => 3734,
                    'unidade_atendidos' => 'estudantes',
                    'valor' => 1380870.24,
                ],
                [
                    'nome' => 'Transporte Escolar Municipal',
                    'bases_legais' => [['numero' => '1.303', 'ano' => 2001]],
                    'ano_criacao' => 2001,
                    'descricao' => 'Deslocamento seguro e gratuito de estudantes entre residência e instituição de ensino, assegurando acesso, permanência e frequência escolar.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['estudantes', 'criancas_adolescentes'],
                    'qtd_atendidos' => 2434,
                    'unidade_atendidos' => 'estudantes',
                    'detalhe_atendidos' => '1.115 da rede municipal + 1.319 da rede estadual',
                    'valor' => 10961577.39,
                ],
                [
                    'nome' => 'Transporte Escolar Intermunicipal',
                    'bases_legais' => [['numero' => '1.303', 'ano' => 2001]],
                    'ano_criacao' => 2001,
                    'descricao' => 'Transporte escolar intermunicipal para alunos do Ensino Médio, Superior, cursos profissionalizantes, supletivos e pós-graduação, mediante comprovação de matrícula e frequência.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['estudantes'],
                    'qtd_atendidos' => 628,
                    'unidade_atendidos' => 'estudantes',
                    'valor' => 6434909.07,
                ],
                [
                    'nome' => 'Programa Educacional de Resistência às Drogas (PROERD)',
                    'bases_legais' => [['tipo' => 'Convênio', 'numero' => '166', 'ano' => 2022]],
                    'ano_criacao' => 2022,
                    'descricao' => 'Ações educativas de prevenção ao uso de drogas e à violência para alunos do 5º ano, em parceria com a Secretaria de Segurança Pública do Estado, com formatura ao final das atividades.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['estudantes', 'criancas_adolescentes'],
                    'fonte_recurso' => FonteRecurso::Convenio,
                    'qtd_atendidos' => 376,
                    'unidade_atendidos' => 'estudantes',
                    'detalhe_atendidos' => 'do 5º ano',
                    'valor' => 62093.44,
                ],
                [
                    'nome' => 'Talentos de Santa Helena',
                    'bases_legais' => [['numero' => '3.317', 'ano' => 2025]],
                    'ano_criacao' => 2025,
                    'descricao' => 'Fomento e valorização da participação de artistas locais em eventos e ações culturais municipais, fortalecendo a identidade cultural e a profissionalização artística.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['artistas'],
                    'qtd_atendidos' => 91,
                    'unidade_atendidos' => 'artistas',
                    'valor' => 163800.00,
                ],
                [
                    'nome' => 'Merenda Escolar',
                    'bases_legais' => [['tipo' => 'Lei Federal', 'numero' => '11.947', 'ano' => 2009]],
                    'ano_criacao' => 2009,
                    'descricao' => 'Alimentação adequada e saudável aos alunos da educação básica pública durante o período letivo, conforme diretrizes do PNAE.',
                    'mecanismo' => Mecanismo::ServicoPublico,
                    'publico_alvo' => ['estudantes', 'criancas_adolescentes'],
                    'fonte_recurso' => FonteRecurso::NaoInformado,
                    'qtd_atendidos' => 3789,
                    'unidade_atendidos' => 'estudantes',
                    'detalhe_atendidos' => '3.652 da rede municipal + 137 da APAE',
                    'valor' => 2625604.75,
                    'observacao_interna' => 'Informar quanto do valor é repasse federal (PNAE) e quanto é complemento municipal.',
                ],
            ],
        ];
    }
}
