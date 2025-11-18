<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index()
    {
        return StockMovement::with(['product', 'supplier'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric',
            'unit_price' => 'nullable|numeric',
            'movement_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $movement = StockMovement::create($data);

        // Update product stock
        $product = Product::find($data['product_id']);

        if ($data['type'] === 'in') {
            $product->current_stock += $data['quantity'];
        } else {
            $product->current_stock -= $data['quantity'];
        }

        $product->save();

        return response()->json($movement);
    }
}
