<?php

namespace App\Http\Controllers;

use App\Repositories\ReportRepository;
use App\Models\Category;
use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $repository;

    public function __construct(ReportRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $filters = $request->all();

        // Datas padrão: início do ano até hoje ou mês atual
        if (!isset($filters['date_from'])) {
            $filters['date_from'] = Carbon::now()->startOfMonth()->format('Y-m-d');
            $filters['date_to'] = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $this->repository->buildQuery($filters, Auth::id());

        $activeTab = $request->input('tab', 'extrato');
        $reportData = null;
        $summary = $this->repository->getSummaryCards();

        switch ($activeTab) {
            case 'extrato':
                $reportData = $this->repository->getStatement();
                break;
            case 'categoria':
                $reportData = $this->repository->getByCategory();
                break;
            case 'metas':
                $reportData = $this->repository->getGoalHistory();
                break;
            case 'evolucao':
                $reportData = $this->repository->getMonthlyEvolution();
                break;
        }

        return Inertia::render('Relatorios', [
            'filters' => $filters,
            'activeTab' => $activeTab,
            'reportData' => $reportData,
            'summary' => $summary,
            'categories' => Category::where('user_id', Auth::id())->get(),
            'accounts' => Account::where('user_id', Auth::id())->get(),
        ]);
    }

    public function export(Request $request)
    {
        // Lógica simples de CSV
        $filters = $request->all();
        $this->repository->buildQuery($filters, Auth::id());
        $data = $this->repository->getAllForExport();

        $fileName = 'relatorio_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Data', 'Descricao', 'Tipo', 'Valor', 'Categoria', 'Conta']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->date,
                    $row->description,
                    $row->type,
                    $row->amount,
                    $row->category->name ?? 'N/A',
                    $row->account->name ?? 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
