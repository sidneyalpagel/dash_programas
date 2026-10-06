<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

// Sem ano no endereço: o exercício escolhido no painel. Com /{ano}/: os demais exercícios.
$paginas = function () {
    Route::get('/', 'inicio')->name('inicio');
    Route::get('/programas', 'programas')->name('programas.index');
    Route::get('/programas/{slug}', 'programa')->name('programas.show');
    Route::get('/entenda', 'entenda')->name('entenda');
};

Route::controller(SiteController::class)->group($paginas);

// Pré-visualização de um rascunho, só para quem pode editá-lo no painel.
Route::get('/previa/programas/{programa:id}', [SiteController::class, 'previa'])
    ->middleware('auth')
    ->name('previa.programa');

Route::controller(SiteController::class)
    ->prefix('{ano}')
    ->whereNumber('ano')
    ->name('ano.')
    ->group($paginas);
