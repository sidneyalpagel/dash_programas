<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class GuiaRapido extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.guia-rapido';
}
