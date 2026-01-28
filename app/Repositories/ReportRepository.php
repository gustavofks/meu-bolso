<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\GoalTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportRepository
{
    protected $query;
    protected $userId;
    protected $filters;

    public function buildQuery($filters, $userId)
    {
        $this->userId = $userId;
        $this->filters = $filters;

        $this->query = Transaction::where('user_id', $userId)
            ->with(['category', 'account', 'paymentMethod']);

        // Filtros Globais
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $this->query->whereBetween('date', [$filters['date_from'], $filters['date_to']]);
        } else {
            $this->query->whereMonth('date', Carbon::now()->month)
                        ->whereYear('date', Carbon::now()->year);
        }

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

    // Cards de resumo
    public function getSummaryCards()
    {
        // Calcula saldo anterior ao período filtrado
        $previousBalance = 0;
        if (!empty($this->filters['date_from'])) {
            $previousBalance = Transaction::where('user_id', $this->userId)
                ->where('date', '<', $this->filters['date_from'])
                ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as saldo")
                ->value('saldo') ?? 0;
        }

        $income = (clone $this->query)->where('type', 'income')->sum('amount');
        $expense = (clone $this->query)->where('type', 'expense')->sum('amount');

        return [
            'previous_balance' => $previousBalance,
            'income' => $income,
            'expense' => $expense,
            'balance' => $previousBalance + ($income - $expense)
        ];
    }

    // Lógica para query reutilizável

    // Query Extrato Detalhado
    public function getStatementQuery()
    {
        $select = "
            transactions.*,
            SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END)
            OVER (ORDER BY date ASC, id ASC) as running_balance
        ";
        return (clone $this->query)
            ->selectRaw($select)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');
    }

    // Query Histórico de Metas
    public function getGoalHistoryQuery()
    {
        $q = GoalTransaction::query()
            ->join('goals', 'goal_transactions.goal_id', '=', 'goals.id')
            ->leftJoin('accounts', 'goals.account_id', '=', 'accounts.id')
            ->where('goals.user_id', $this->userId)
            ->select(
                'goal_transactions.*',
                'goals.name as meta_name',
                'accounts.name as account_name'
            );

        if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
            $q->whereBetween('goal_transactions.date', [$this->filters['date_from'], $this->filters['date_to']]);
        }

        return $q->latest('goal_transactions.date');
    }

    // Coleção de Categorias
    public function getByCategoryCollection()
    {
        $totalExpenses = (clone $this->query)->where('type', 'expense')->sum('amount');

        return (clone $this->query)
            ->where('type', 'expense')
            ->selectRaw('category_id, COUNT(*) as qtd, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) use ($totalExpenses) {
                return (object) [ // Convertendo para objeto para padronizar acesso
                    'name' => $item->category ? $item->category->name : 'Sem Categoria',
                    'type' => $item->type,
                    'total' => (float)$item->total,
                    'qtd' => $item->qtd,
                    'percent' => $totalExpenses > 0 ? round(($item->total / $totalExpenses) * 100, 1) : 0
                ];
            });
    }

    // Coleção de Evolução
    public function getMonthlyEvolutionCollection()
    {
        return (clone $this->query)
            ->selectRaw("
                TO_CHAR(date, 'MM/YYYY') as mes_ano,
                TO_CHAR(date, 'YYYY-MM') as sort_date,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as entradas,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as saidas
            ")
            ->groupByRaw("TO_CHAR(date, 'MM/YYYY'), TO_CHAR(date, 'YYYY-MM')")
            ->orderBy('sort_date')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'mes_ano' => $item->mes_ano,
                    'entradas' => (float)$item->entradas,
                    'saidas' => (float)$item->saidas,
                    'resultado' => (float)$item->entradas - (float)$item->saidas
                ];
            });
    }

    // Paginação
    public function getStatementPaginated() {
        return $this->getStatementQuery()->paginate(10)->withQueryString();
    }

    public function getGoalHistoryPaginated() {
        return $this->getGoalHistoryQuery()->paginate(10)->withQueryString();
    }

    // Export
    public function getDataForExport($tab)
    {
        switch ($tab) {
            case 'categoria':
                return $this->getByCategoryCollection();
            case 'metas':
                return $this->getGoalHistoryQuery()->get();
            case 'evolucao':
                return $this->getMonthlyEvolutionCollection();
            case 'extrato':
            default:
                return $this->getStatementQuery()->get();
        }
    }
}
