<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Expense;          
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class StockMovementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index function return list off all stock movement desc 
    |--------------------------------------------------------------------------
    */
    public function index(): JsonResponse
    {
        $movements = StockMovement::with(['product', 'supplier'])
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($movements);
    }
    /*
    |--------------------------------------------------------------------------
    | store function create a new stock movement  
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'type'          => 'required|in:in,out',
            'quantity'      => 'required|numeric|min:0.01',
            'unit_price'    => 'nullable|numeric|min:0', 
            'movement_date' => 'nullable|date',
            'reason'        => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $qty     = $data['quantity'];

        $data['movement_date'] = $data['movement_date']
            ?? Carbon::now()->toDateString();

        // ---------- OUT movement ----------
        if ($data['type'] === 'out') {

            if ($product->current_stock < $qty) {
                return response()->json([
                    'message' => 'Not enough stock for this product.',
                ], 422);
            }

            $product->current_stock -= $qty;

            
        // ---------- IN movement ----------
        } else {

            $unitPrice = $data['unit_price'];

            if ($unitPrice === null) {
                return response()->json([
                    'message' => 'unit_price is required for stock IN.',
                ], 422);
            }

            $currentQty   = $product->current_stock;
            $currentCost  = $product->average_cost;
            $incomingQty  = $qty;
            $incomingCost = $unitPrice;

            $newQty = $currentQty + $incomingQty;

            if ($newQty > 0) {

                $newAvgCost = ( ($currentQty * $currentCost) + ($incomingQty * $incomingCost) ) / $newQty;

            } else {

                $newAvgCost = $incomingCost;

            }

            $product->current_stock = $newQty;
            $product->average_cost  = $newAvgCost;
        }

        $product->save();

        $movement = StockMovement::create($data);

        if (
            $movement->type === 'in' &&
            $movement->supplier_id &&
            $movement->unit_price !== null
        ) {
            $totalCost = $movement->quantity * $movement->unit_price;

            Expense::create([
                'category'     => 'Stock purchase In', 
                'amount'       => $totalCost,
                'expense_date' => $movement->movement_date ?? Carbon::now()->toDateString(),
                'supplier_id'  => $movement->supplier_id,
                'notes'        => 'Auto from stock movement',
            ]);
        }

        return response()->json(
            $movement->load(['product', 'supplier']),
            201
        );
    }
}
