<?php

namespace App\Http\Controllers;

use App\Repositories\ReportRepository;
use App\Models\Category;
use App\Models\Account;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

        // Switch para buscar dados paginados para a tela
        switch ($activeTab) {
            case 'categoria':
                $reportData = $this->repository->getByCategoryCollection();
                break;
            case 'metas':
                $reportData = $this->repository->getGoalHistoryPaginated();
                break;
            case 'evolucao':
                $reportData = $this->repository->getMonthlyEvolutionCollection();
                break;
            case 'extrato':
            default:
                $reportData = $this->repository->getStatementPaginated();
                break;
        }

        return Inertia::render('Relatorios', [
            'filters' => $filters,
            'activeTab' => $activeTab,
            'reportData' => $reportData,
            'summary' => $this->repository->getSummaryCards(),
            'categories' => Category::where('user_id', Auth::id())->get(),
            'accounts' => Account::where('user_id', Auth::id())->get(),
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->all();
        $this->repository->buildQuery($filters, Auth::id());

        $tab = $request->input('tab', 'extrato');
        $format = $request->input('format', 'csv'); // 'csv' ou 'xlsx'

        $data = $this->repository->getDataForExport($tab);
        $fileName = 'relatorio_' . $tab . '_' . date('Y-m-d_Hi');

        $columns = [];
        $rowCallback = null;

        // Configuração das colunas
        switch ($tab) {
            case 'categoria':
                $columns = ['Categoria', 'Tipo', 'Qtd Transações', 'Total (R$)', '% do Total'];
                $rowCallback = function($row) {
                    return [
                        $row->name,
                        $row->type === 'income' ? 'Entrada' : 'Saída',
                        $row->qtd,
                        $row->total,
                        $row->percent / 100
                    ];
                };
                break;
            case 'metas':
                $columns = ['Data', 'Nome da Meta', 'Conta Origem', 'Valor Movimentado', 'Saldo Após'];
                $rowCallback = function($row) {
                    return [
                        Carbon::parse($row->date)->format('d/m/Y'),
                        $row->meta_name,
                        $row->account_name ?? 'N/A',
                        $row->amount,
                        $row->balance_after
                    ];
                };
                break;
            case 'evolucao':
                $columns = ['Mês/Ano', 'Total Entradas', 'Total Saídas', 'Resultado'];
                $rowCallback = function($row) {
                    return [
                        $row->mes_ano,
                        $row->entradas,
                        $row->saidas,
                        $row->resultado
                    ];
                };
                break;
            case 'extrato':
            default:
                $columns = ['Data', 'Descrição', 'Categoria', 'Conta', 'Forma Pagamento', 'Tipo', 'Valor (R$)'];
                $rowCallback = function($row) {
                    return [
                        Carbon::parse($row->date)->format('d/m/Y'),
                        $row->description,
                        $row->category->name ?? 'Sem Categoria',
                        $row->account->name ?? 'Sem Conta',
                        $row->paymentMethod->name ?? '-',
                        $row->type === 'income' ? 'Entrada' : 'Saída',
                        ($row->type === 'income' ? 1 : -1) * $row->amount
                    ];
                };
                break;
        }

        // Formato xlsx ou csv
        if ($format === 'xlsx') {
            return $this->exportXlsx($data, $columns, $rowCallback, $fileName, $tab);
        } else {
            return $this->exportCsv($data, $columns, $rowCallback, $fileName);
        }
    }


    // Gera e baixa o arquivo Excel Formatado
    private function exportXlsx($data, $headers, $callback, $fileName, $tab)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Cabeçalho
        $sheet->fromArray([$headers], null, 'A1');

        // Dados
        $rows = [];
        foreach ($data as $item) {
            $rows[] = $callback($item);
        }

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }

        // Formatação
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Estilo do Cabeçalho
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']], // Cinza escuro
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray($headerStyle);

        // Auto-size nas colunas
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $currencyFormat = '"R$ "#,##0.00_-';

        if ($tab === 'extrato') {
            // Coluna G (Valor)
            $sheet->getStyle("G2:G{$lastRow}")->getNumberFormat()->setFormatCode($currencyFormat);
            // Coluna A (Data) Centralizada
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        elseif ($tab === 'categoria') {
            // Coluna D (Total) - Moeda
            $sheet->getStyle("D2:D{$lastRow}")->getNumberFormat()->setFormatCode($currencyFormat);
            // Coluna E (%) - Porcentagem
            $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
        }
        elseif ($tab === 'metas') {
            // Coluna D e E (Valores)
            $sheet->getStyle("D2:E{$lastRow}")->getNumberFormat()->setFormatCode($currencyFormat);
        }
        elseif ($tab === 'evolucao') {
            // Colunas B, C, D (Valores)
            $sheet->getStyle("B2:D{$lastRow}")->getNumberFormat()->setFormatCode($currencyFormat);
        }

        // Download
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Gera e baixa o arquivo .csv
    private function exportCsv($data, $headers, $callback, $fileName)
    {
        $headersHttp = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream(function() use($data, $headers, $callback) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM para Excel ler acentos no CSV
            fputcsv($file, $headers, ';');

            foreach ($data as $row) {
                $csvRow = $callback($row);

                // Formata números para o padrão Brasileiro no CSV
                $csvRow = array_map(function($item) {
                    if (is_numeric($item) && !is_string($item)) {
                        return number_format($item, 2, ',', '.');
                    }
                    return $item;
                }, $csvRow);

                fputcsv($file, $csvRow, ';');
            }
            fclose($file);
        }, 200, $headersHttp);
    }
}
