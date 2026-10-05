<?php

namespace Database\Seeders;

use App\Models\Secretaria;
use Illuminate\Database\Seeder;

class SecretariaSeeder extends Seeder
{
    public function run(): void
    {
        $secretarias = [
            [
                'slug' => 'agricultura',
                'nome' => 'Secretaria Municipal de Agricultura e Abastecimento Rural',
                'nome_curto' => 'Agricultura',
                'cor' => '#1e7145',
                'apresentacao' => 'Apoia quem produz no campo: correção do solo, máquinas, melhoria genética do rebanho, compra de alimentos da agricultura familiar e incentivos para novos investimentos.',
                'endereco' => 'Rua Paraguai, 1401 - Santa Helena - PR',
                'telefone' => '(45) 3268-8200',
            ],
            [
                'slug' => 'desenvolvimento-economico',
                'nome' => 'Secretaria Municipal de Desenvolvimento Econômico',
                'nome_curto' => 'Desenvolvimento Econômico',
                'cor' => '#1d3a6b',
                'apresentacao' => 'Apoia empresas e empreendedores com incentivo financeiro, orientação, cursos e acesso a linhas de crédito.',
            ],
            [
                'slug' => 'esportes',
                'nome' => 'Secretaria Municipal de Esportes e Lazer',
                'nome_curto' => 'Esportes e Lazer',
                'cor' => '#b8860b',
                'apresentacao' => 'Apoia atletas e entidades esportivas do Município, com bolsas e parcerias.',
            ],
            [
                'slug' => 'assistencia-social',
                'nome' => 'Secretaria Municipal de Assistência Social',
                'nome_curto' => 'Assistência Social',
                'cor' => '#8b3a45',
                'apresentacao' => 'Atende famílias em situação de vulnerabilidade, idosos, mulheres, crianças e adolescentes com benefícios, moradia, acolhimento e atividades.',
            ],
            [
                'slug' => 'educacao',
                'nome' => 'Secretaria Municipal de Educação e Cultura',
                'nome_curto' => 'Educação e Cultura',
                'cor' => '#2e5d8c',
                'apresentacao' => 'Garante transporte, merenda e uniforme aos estudantes, oferece bolsas e atendimento especializado, e valoriza os artistas locais.',
            ],
        ];

        foreach ($secretarias as $ordem => $dados) {
            Secretaria::updateOrCreate(['slug' => $dados['slug']], $dados + ['ordem' => $ordem + 1]);
        }
    }
}
