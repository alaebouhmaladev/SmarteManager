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
        $data = $request->validate([
            'name' => 'required',
            'sku' => 'nullable',
            'unit' => 'required',
            'current_stock' => 'numeric',
            'min_stock' => 'numeric',
            'average_cost' => 'nullable|numeric',
        ]);

        return Product::create($data);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());

        return $product;
    }

    public function destroy($id)
    {
        Product::destroy($id);
        return ['message' => 'Product deleted'];
    }
}
