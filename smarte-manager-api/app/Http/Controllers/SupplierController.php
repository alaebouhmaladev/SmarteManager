<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Expense;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index function return all suppliers 
    |--------------------------------------------------------------------------
    */
    public function index(): JsonResponse
    {

        $suppliers = Supplier::orderBy('name')->get();

        
        $expensesBySupplier = Expense::selectRaw('supplier_id, SUM(amount) AS total')
            ->groupBy('supplier_id')
            ->pluck('total', 'supplier_id'); 

    
        $purchasesBySupplier = StockMovement::where('type', 'in')
            ->selectRaw('supplier_id, SUM(quantity * unit_price) AS total')
            ->groupBy('supplier_id')
            ->pluck('total', 'supplier_id');

        
        foreach ($suppliers as $supplier) {
            $expensesTotal  = $expensesBySupplier[$supplier->id] ?? 0;
            $purchasesTotal = $purchasesBySupplier[$supplier->id] ?? 0;

            
            $supplier->total_purchases = round($purchasesTotal, 2);

            
            $supplier->total_spent = round($expensesTotal + $purchasesTotal, 2);
        }

        return response()->json($suppliers);
    }
    /*
    |--------------------------------------------------------------------------
    | store function create a new supplier
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required',
            'contact_name' => 'nullable',
            'phone'        => 'nullable',
            'email'        => 'nullable|email',
            'address'      => 'nullable',
        ]);

        return Supplier::create($data);
    }
    /*
    |--------------------------------------------------------------------------
    | show function return supplier data using id 
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        return Supplier::findOrFail($id);
    }
    /*
    |--------------------------------------------------------------------------
    | update function updating supplier using id 
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        return $supplier;
    }
    /*
    |--------------------------------------------------------------------------
    | destroy function delete supplier using id 
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        Supplier::destroy($id);
        return ['message' => 'Supplier deleted'];
    }

    /*
    |--------------------------------------------------------------------------
    | overview function return data about supplier using id 
    |--------------------------------------------------------------------------
    */
    public function overview($supplierId): JsonResponse
    {
        $supplier = Supplier::findOrFail($supplierId);

        $expenses = Expense::where('supplier_id', $supplierId)
            ->orderBy('expense_date', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        $purchases = StockMovement::where('supplier_id', $supplierId)
            ->where('type', 'in')
            ->orderBy('movement_date', 'desc')
            ->get();

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
