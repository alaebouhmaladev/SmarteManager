<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * List all expenses (latest first).
     */
    public function index(): JsonResponse
    {
        $expenses = Expense::with('supplier')
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($expenses);
    }

    /**
     * Show a single expense.
     */
    public function show($id): JsonResponse
    {
        $expense = Expense::with('supplier')->findOrFail($id);

        return response()->json($expense);
    }

    /**
     * Create a new expense.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category'     => 'required|string|max:255',
            'amount'       => 'required|numeric',
            'expense_date' => 'required|date',
            'supplier_id'  => 'nullable|exists:suppliers,id',
            'notes'        => 'nullable|string',
        ]);

        $expense = Expense::create($data);

        return response()->json(
            $expense->load('supplier'),
            201
        );
    }

    /**
     * Update an existing expense.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'category'     => 'required|string|max:255',
            'amount'       => 'required|numeric',
            'expense_date' => 'required|date',
            'supplier_id'  => 'nullable|exists:suppliers,id',
            'notes'        => 'nullable|string',
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update($data);

        return response()->json($expense->load('supplier'));
    }

    /**
     * Delete an expense.
     */
    public function destroy($id): JsonResponse
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted',
        ]);
    }

    /**
     * Monthly summary:
     * GET /api/expenses/monthly-summary?month=2025-11
     * Returns:
     * - total for month
     * - totals per category
     */
    public function monthlySummary(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m', // e.g. 2025-11
        ]);

        $monthParam = $request->query('month') ?? Carbon::now()->format('Y-m');
        [$year, $month] = explode('-', $monthParam);

        $query = Expense::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month);

        $allExpenses = $query->get();

        $total = $allExpenses->sum('amount');

        // Group by category
        $byCategory = $allExpenses->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'total'    => $items->sum('amount'),
            ];
        })->values();

        return response()->json([
            'month'       => $monthParam,
            'total'       => $total,
            'by_category' => $byCategory,
        ]);
    }

    /**
     * Expenses by supplier:
     * GET /api/expenses/by-supplier/{supplier}?from=&to=
     */
    public function bySupplier(Request $request, $supplierId): JsonResponse
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : null;
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : null;

        $query = Expense::with('supplier')
            ->where('supplier_id', $supplierId)
            ->orderBy('expense_date', 'desc');

        if ($from) {
            $query->whereDate('expense_date', '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate('expense_date', '<=', $to->toDateString());
        }

        $expenses = $query->get();
        $total = $expenses->sum('amount');

        return response()->json([
            'supplier_id' => (int) $supplierId,
            'from'        => $from?->toDateString(),
            'to'          => $to?->toDateString(),
            'total'       => $total,
            'expenses'    => $expenses,
        ]);
    }

    /**
     * Export monthly expenses as CSV (optional).
     * GET /api/expenses/export-csv?month=2025-11
     */
    public function exportMonthlyCsv(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $monthParam = $request->query('month') ?? Carbon::now()->format('Y-m');
        [$year, $month] = explode('-', $monthParam);

        $expenses = Expense::with('supplier')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->orderBy('expense_date')
            ->get();

        $filename = "expenses_{$monthParam}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($expenses) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Date',
                'Category',
                'Amount',
                'Supplier',
                'Notes',
            ]);

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->expense_date,
                    $expense->category,
                    $expense->amount,
                    optional($expense->supplier)->name,
                    $expense->notes,
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
