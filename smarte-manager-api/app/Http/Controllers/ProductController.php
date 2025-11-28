<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        // We only allow basic product info + min_stock.
        // Stock & average cost will be managed via StockMovementController.
        $data = $request->validate([
            'name'      => 'required',
            'sku'       => 'nullable',
            'unit'      => 'required',
            'min_stock' => 'numeric',
        ]);

        // New products start with zero stock and zero average cost.
        $data['current_stock'] = 0;
        $data['average_cost']  = 0;

        return Product::create($data);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Do NOT allow editing current_stock / average_cost here.
        $data = $request->validate([
            'name'      => 'required',
            'sku'       => 'nullable',
            'unit'      => 'required',
            'min_stock' => 'numeric',
        ]);

        $product->update($data);

        return $product;
    }

    public function destroy($id)
    {
        Product::destroy($id);

        return ['message' => 'Product deleted'];
    }
}
