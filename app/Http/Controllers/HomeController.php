<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Account;
use App\Models\PaymentMethod;
use App\Models\Tag;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $period = $request->query('period', 'month');

        // Query Base com Filtros de Data
        $query = Transaction::where('user_id', $userId);

        if ($period === 'today') {
            $query->whereDate('date', Carbon::today());
        } else {
            // Padrão: Mês atual
            $query->whereMonth('date', Carbon::now()->month)
                  ->whereYear('date', Carbon::now()->year);
        }

        // A query base é duplicada para calcular os totais e as listas
        $baseQuery = clone $query;

        // Totais do Período
        $totalEntradas = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $totalSaidas = (clone $baseQuery)->where('type', 'expense')->sum('amount');

        $saldoTotal = $totalEntradas - $totalSaidas;

        $entradas = (clone $baseQuery)
            ->where('type', 'income')
            ->with(['category', 'tags'])
            ->latest('date')
            ->get();

        $saidas = (clone $baseQuery)
            ->where('type', 'expense')
            ->with(['category', 'tags'])
            ->latest('date')
            ->get();

        // Dados Auxiliares para o Modal
        return Inertia::render('Home', [
            'dbEntradas'      => $entradas,
            'dbSaidas'        => $saidas,
            'totalEntradas'   => (float)$totalEntradas,
            'totalSaidas'     => (float)$totalSaidas,
            'saldoTotal'      => (float)$saldoTotal,
            'categories'      => Category::where('user_id', $userId)->get(),
            'accounts'        => Account::where('user_id', $userId)->get(),
            'payment_methods' => PaymentMethod::where('user_id', $userId)->get(),
            'tags'            => Tag::where('user_id', $userId)->get(),
            'currentPeriod'   => $period
        ]);
    }
}
