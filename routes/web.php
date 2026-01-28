<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CadastroController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;

// Grupo Principal: Requer Autenticação e Verificação
Route::middleware(['auth', 'verified'])->group(function () {

    // --- Páginas Principais ---
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/cadastros', [CadastroController::class, 'index'])->name('cadastros');
    Route::get('/metas', [GoalController::class, 'index'])->name('metas');

    // --- Módulo: Categorias ---
    Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // --- Módulo: Contas (Accounts) ---
    Route::group(['prefix' => 'accounts', 'as' => 'accounts.'], function () {
        Route::post('/', [AccountController::class, 'store'])->name('store');
        Route::put('/{account}', [AccountController::class, 'update'])->name('update');
        Route::delete('/{account}', [AccountController::class, 'destroy'])->name('destroy');
    });

    // --- Módulo: Formas de Pagamento ---
    Route::group(['prefix' => 'payment-methods', 'as' => 'payment-methods.'], function () {
        Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
        Route::put('/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('update');
        Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('destroy');
    });

    // --- Módulo: Tags ---
    Route::group(['prefix' => 'tags', 'as' => 'tags.'], function () {
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::put('/{tag}', [TagController::class, 'update'])->name('update');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
    });

    // --- Módulo: Metas (Goals) ---
    Route::group(['prefix' => 'goals', 'as' => 'goals.'], function () {
        Route::post('/', [GoalController::class, 'store'])->name('store');
        Route::put('/{goal}', [GoalController::class, 'update'])->name('update');
        Route::delete('/{goal}', [GoalController::class, 'destroy'])->name('destroy');
    });

    // --- Módulo: Transações ---
    Route::group(['prefix' => 'transactions', 'as' => 'transactions.'], function () {
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
    });

    // --- Módulo: Relatórios ---
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
    	Route::get('/', [ReportController::class, 'index'])->name('index');
    	Route::get('/export', [ReportController::class, 'export'])->name('export');
    });

});

// Rotas de Autenticação do Breeze (Login, Register, etc.)
require __DIR__.'/auth.php';
