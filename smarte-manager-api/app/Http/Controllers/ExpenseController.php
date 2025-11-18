<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        return Expense::with('supplier')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable',
        ]);

        return Expense::create($data);
    }
}
