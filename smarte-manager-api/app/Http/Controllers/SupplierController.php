<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
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
}
