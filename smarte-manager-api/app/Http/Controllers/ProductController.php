<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index function return all products 
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return Product::all();
    }

    /*
    |--------------------------------------------------------------------------
    | store function create new product
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {

        $data = $request->validate([
            'name'      => 'required',
            'sku'       => 'nullable',
            'unit'      => 'required',
            'min_stock' => 'numeric',
        ]);

        $data['current_stock'] = 0;
        $data['average_cost']  = 0;

        return Product::create($data);
    }
    /*
    |--------------------------------------------------------------------------
    | update function updateing product with id 
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name'      => 'required',
            'sku'       => 'nullable',
            'unit'      => 'required',
            'min_stock' => 'numeric',
        ]);

        $product->update($data);

        return $product;
    }

    /*
    |--------------------------------------------------------------------------
    | destroy function delete product with id 
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        Product::destroy($id);

        return ['message' => 'Product deleted'];
    }
}
