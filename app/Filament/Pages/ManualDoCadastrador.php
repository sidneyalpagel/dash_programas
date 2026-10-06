<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Manual de operação para quem cadastra os programas. O texto fica em
 * resources/manual/manual-do-cadastrador.md e é publicado junto com o sistema.
 */
class ManualDoCadastrador extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Manual do cadastrador';

    protected static ?string $title = 'Manual do cadastrador';

    protected static ?string $slug = 'manual';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.manual';

    public function getSubheading(): ?string
    {
        return 'Como cadastrar, conferir, publicar e atualizar os programas da sua secretaria.';
    }

    /** Conteúdo em HTML, com o diagrama do fluxo no lugar da marcação [[diagrama-fluxo]]. */
    public function conteudo(): HtmlString
    {
        $markdown = file_get_contents(resource_path('manual/manual-do-cadastrador.md'));

        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        // Cada seção ganha uma âncora para o sumário (## 1. Acesso ao painel → #acesso-ao-painel).
        $html = preg_replace_callback('/<h2>(.*?)<\/h2>/', function (array $m) {
            $id = Str::slug(preg_replace('/^\d+\.\s*/', '', strip_tags($m[1])));

            return "<h2 id=\"{$id}\">{$m[1]}</h2>";
        }, $html);

        $html = str_replace('<p>[[diagrama-fluxo]]</p>', view('filament.pages.manual-diagrama')->render(), $html);

        return new HtmlString($html);
    }

    /** @return list<array{id: string, titulo: string}> */
    public function sumario(): array
    {
        preg_match_all('/^## (.+)$/m', file_get_contents(resource_path('manual/manual-do-cadastrador.md')), $m);

        return array_map(fn (string $titulo) => [
            'id' => Str::slug(preg_replace('/^\d+\.\s*/', '', $titulo)),
            'titulo' => $titulo,
        ], $m[1]);
    }
}
