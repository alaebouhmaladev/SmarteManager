<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Expense;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class SupplierController extends Controller
{
    public function index() 
    {
        return Supplier::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'contact_name' => 'nullable',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable',
        ]);

        return Supplier::create($data);
    }

    public function show($id)
    {
        return Supplier::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        return $supplier;
    }

    public function destroy($id)
    {
        Supplier::destroy($id);
        return ['message' => 'Supplier deleted'];
    }

    /**
     * GET /api/suppliers/{supplier}/overview
     * Full supplier dashboard:
     * - supplier info
     * - all expenses with this supplier
     * - all purchases (stock IN movements)
     * - total spent
     * - total purchases amount
     */
    public function overview($supplierId): JsonResponse
    {
        $supplier = Supplier::findOrFail($supplierId);

        // Get all expenses from this supplier
        $expenses = Expense::where('supplier_id', $supplierId)
            ->orderBy('expense_date', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // Get all stock purchases (IN movements only)
        $purchases = StockMovement::where('supplier_id', $supplierId)
            ->where('type', 'in')
            ->orderBy('movement_date', 'desc')
            ->get();

        // Total purchase cost = sum(quantity * unit_price)
        $totalPurchases = $purchases->reduce(function ($carry, $item) {
            return $carry + ($item->quantity * $item->unit_price);
        }, 0);

        return response()->json([
            'supplier' => $supplier,
            'totals' => [
                'total_expenses'  => $totalExpenses,
                'total_purchases' => $totalPurchases,
                'total_spent'     => $totalExpenses + $totalPurchases,
            ],
            'expenses'  => $expenses,
            'purchases' => $purchases,
        ]);
    }

}
