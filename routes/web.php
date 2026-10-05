<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::controller(SiteController::class)->group(function () {
    Route::get('/', 'inicio')->name('inicio');
    Route::get('/programas', 'programas')->name('programas.index');
    Route::get('/programas/{programa}', 'programa')->name('programas.show');
    Route::get('/entenda', 'entenda')->name('entenda');
});
