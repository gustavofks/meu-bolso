<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/cadastros', function () {
    return Inertia::render('Cadastros');
})->middleware(['auth', 'verified'])->name('cadastros');

Route::get('/metas', function () {
    return Inertia::render('Metas');
})->middleware(['auth', 'verified'])->name('metas');

require __DIR__.'/auth.php';
