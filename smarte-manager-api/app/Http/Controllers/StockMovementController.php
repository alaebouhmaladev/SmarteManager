<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class StockMovementController extends Controller
{
    public function index(): JsonResponse
    {
        $movements = StockMovement::with(['product', 'supplier'])
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($movements);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'type'          => 'required|in:in,out',
            'quantity'      => 'required|numeric|min:0.01',
            'unit_price'    => 'nullable|numeric|min:0', // for IN
            'movement_date' => 'nullable|date',
            'reason'        => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $qty     = $data['quantity'];

        // Default date = today if not provided
        $data['movement_date'] = $data['movement_date']
            ?? Carbon::now()->toDateString();

        // ---------- OUT movement (remove stock) ----------
        if ($data['type'] === 'out') {
            // Prevent negative stock
            if ($product->current_stock < $qty) {
                return response()->json([
                    'message' => 'Not enough stock for this product.',
                ], 422);
            }

            $product->current_stock -= $qty;
            // average_cost stays same on OUT

        // ---------- IN movement (add stock) ----------
        } else {
            // For IN: unit_price should be provided
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
                // Moving average cost
                $newAvgCost = (
                    ($currentQty * $currentCost) +
                    ($incomingQty * $incomingCost)
                ) / $newQty;
            } else {
                $newAvgCost = $incomingCost;
            }

            $product->current_stock = $newQty;
            $product->average_cost  = $newAvgCost;
        }

        $product->save();

        // Save movement itself
        $movement = StockMovement::create($data);

        return response()->json(
            $movement->load(['product', 'supplier']),
            201
        );
    }
}
