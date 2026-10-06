<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Perfis usados no filtro "Que programas existem para mim?" do site público.
 */
enum PublicoAlvo: string implements HasLabel
{
    case Estudantes = 'estudantes';
    case Familias = 'familias';
    case CriancasAdolescentes = 'criancas_adolescentes';
    case Idosos = 'idosos';
    case Mulheres = 'mulheres';
    case PessoasComDeficiencia = 'pessoas_com_deficiencia';
    case ProdutoresRurais = 'produtores_rurais';
    case Empresas = 'empresas';
    case Trabalhadores = 'trabalhadores';
    case Atletas = 'atletas';
    case Artistas = 'artistas';
    case Comunidades = 'comunidades';

    public function getLabel(): string
    {
        return match ($this) {
            self::Estudantes => 'Estudantes',
            self::Familias => 'Famílias em vulnerabilidade',
            self::CriancasAdolescentes => 'Crianças e adolescentes',
            self::Idosos => 'Idosos',
            self::Mulheres => 'Mulheres',
            self::PessoasComDeficiencia => 'Pessoas com deficiência ou TEA',
            self::ProdutoresRurais => 'Produtores rurais',
            self::Empresas => 'Empresas e empreendedores',
            self::Trabalhadores => 'Quem busca qualificação',
            self::Atletas => 'Atletas e entidades esportivas',
            self::Artistas => 'Artistas',
            self::Comunidades => 'Comunidades',
        };
    }

    /** Frase usada nos botões do site público. */
    public function frase(): string
    {
        return match ($this) {
            self::Estudantes => 'Sou estudante',
            self::Familias => 'Minha família precisa de apoio',
            self::CriancasAdolescentes => 'Para crianças e adolescentes',
            self::Idosos => 'Tenho 60 anos ou mais',
            self::Mulheres => 'Sou mulher',
            self::PessoasComDeficiencia => 'Pessoa com deficiência ou autismo',
            self::ProdutoresRurais => 'Sou produtor(a) rural',
            self::Empresas => 'Tenho uma empresa ou quero abrir',
            self::Trabalhadores => 'Quero fazer um curso',
            self::Atletas => 'Pratico esporte',
            self::Artistas => 'Sou artista',
            self::Comunidades => 'Represento uma comunidade',
        };
    }
}
