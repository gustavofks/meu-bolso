<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportRepository
{
    protected $query;

    public function buildQuery($filters, $userId)
    {
        // Iniciamos a query base. Note que não executamos nada ainda.
        $this->query = Transaction::where('user_id', $userId)
            ->with(['category', 'account', 'paymentMethod']);

        // 1. Filtro de Data
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $this->query->whereBetween('date', [$filters['date_from'], $filters['date_to']]);
        } else {
            $this->query->whereMonth('date', Carbon::now()->month)
                        ->whereYear('date', Carbon::now()->year);
        }

        // 2. Filtros Opcionais
        if (!empty($filters['type']) && $filters['type'] !== 'todos') {
            $this->query->where('type', $filters['type']);
        }

        if (!empty($filters['category_id']) && $filters['category_id'] !== 'todas') {
            $this->query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['account_id']) && $filters['account_id'] !== 'todas') {
            $this->query->where('account_id', $filters['account_id']);
        }

        return $this;
    }

    public function getSummaryCards()
    {
        $income = (clone $this->query)->where('type', 'income')->sum('amount');
        $expense = (clone $this->query)->where('type', 'expense')->sum('amount');

        $balance = $income - $expense;

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance
        ];
    }

    public function getStatement()
    {
        // Clone para garantir paginação limpa
        return (clone $this->query)
            ->latest('date')
            ->paginate(10)
            ->withQueryString();
    }

    public function getByCategory()
    {
        return (clone $this->query)
            ->selectRaw('category_id, type, SUM(amount) as total')
            ->groupBy('category_id', 'type')
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category ? $item->category->name : 'Sem Categoria',
                    'type' => $item->type,
                    'total' => (float)$item->total
                ];
            });
    }

    public function getChartData()
    {
        return (clone $this->query)
            ->selectRaw("CAST(date AS DATE) as dia, type, SUM(amount) as total")
            ->groupByRaw("CAST(date AS DATE), type")
            ->orderBy('dia')
            ->get();
    }

    public function getGoalRegisters()
    {
        // Exemplo: Buscando de uma tabela de transações de metas
        // Ajuste 'GoalTransaction' para o nome real da sua Model
        return DB::table('goal_transactions') // ou GoalTransaction::query()...
            ->join('goals', 'goal_transactions.goal_id', '=', 'goals.id')
            ->where('goals.user_id')
            ->whereBetween('goal_transactions.date', [
                $this->query->getQuery()->bindings['where'][1], // Pega data inicio da query base
                $this->query->getQuery()->bindings['where'][2]  // Pega data fim da query base
            ])
            ->select(
                'goal_transactions.id',
                'goal_transactions.date',
                'goal_transactions.amount',
                'goals.name as meta_name',
                // Se tiver saldo após a transação salvo no banco, use-o, senão calcule
                'goal_transactions.amount as total'
            )
            ->orderBy('goal_transactions.date', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    public function getAllForExport() {
        return (clone $this->query)->latest('date')->get();
    }
}
