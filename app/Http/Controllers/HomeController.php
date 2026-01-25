<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Busca entradas e saídas recentes
        $entradas = Transaction::where('user_id', $userId)
            ->whereHas('category', fn($q) => $q->where('type', 'income'))
            ->with('category')
            ->latest('date')
            ->take(5)
            ->get();

        $saidas = Transaction::where('user_id', $userId)
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->with('category')
            ->latest('date')
            ->take(5)
            ->get();

        // Totais para os cards
        $totalEntradas = Transaction::where('user_id', $userId)
            ->whereHas('category', fn($q) => $q->where('type', 'income'))
            ->sum('amount');

        $totalSaidas = Transaction::where('user_id', $userId)
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->sum('amount');

        return Inertia::render('Home', [
            'dbEntradas' => $entradas,
            'dbSaidas' => $saidas,
            'totalEntradas' => (float)$totalEntradas,
            'totalSaidas' => (float)$totalSaidas,
            'saldoTotal' => (float)($totalEntradas - $totalSaidas),
        ]);
    }
}
