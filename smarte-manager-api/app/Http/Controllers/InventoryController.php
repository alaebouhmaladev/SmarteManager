<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use Carbon\Carbon;


class InventoryController extends Controller
{
    /**
     * GET /api/inventory/overview
     * List all products with stock + value.
     */
    public function overview(): JsonResponse
    {
        $products = Product::select(
                'id',
                'name',
                'sku',
                'unit',
                'current_stock',
                'min_stock',
                'average_cost'
            )
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $product->stock_value = round(
                    $product->current_stock * $product->average_cost,
                    2
                );
                return $product;
            });

        return response()->json($products);
    }

    /**
     * GET /api/inventory/low-stock
     * Products where current_stock <= min_stock.
     */
    public function lowStock(): JsonResponse
    {
        $products = Product::whereColumn('current_stock', '<=', 'min_stock')
            ->orderBy('current_stock')
            ->get([
                'id',
                'name',
                'sku',
                'unit',
                'current_stock',
                'min_stock',
                'average_cost',
            ]);

        return response()->json($products);
    }

    /**
     * GET /api/inventory/valuation
     * Total value of inventory (current_stock * average_cost).
     */
    public function valuation(): JsonResponse
    {
        $total = Product::select(
                DB::raw('SUM(current_stock * average_cost) as total_value')
            )
            ->value('total_value') ?? 0;

        return response()->json([
            'total_value' => round($total, 2),
        ]);
    }

    /**
     * GET /api/inventory/average-cost
     * Returns only product average costs.
     */
    public function averageCost(): JsonResponse
    {
        $products = Product::select(
                'id',
                'name',
                'sku',
                'unit',
                'average_cost'
            )
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    /**
     * GET /api/inventory/product/{product}/history
     * Optional query params: from=Y-m-d, to=Y-m-d
     */
    public function productHistory(Request $request, $productId): JsonResponse
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : null;

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : null;

        // Base query: all movements for this product
        $query = StockMovement::with('supplier')
            ->where('product_id', $productId)
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc');

        // Apply date range if provided
        if ($from && $to) {
            $query->whereBetween('movement_date', [
                $from->toDateString(),
                $to->toDateString(),
            ]);
        } elseif ($from) {
            $query->whereDate('movement_date', '>=', $from->toDateString());
        } elseif ($to) {
            $query->whereDate('movement_date', '<=', $to->toDateString());
        }

        $movements = $query->get();

        // Include basic product info
        $product = Product::select('id', 'name', 'sku', 'unit', 'current_stock', 'average_cost')
            ->findOrFail($productId);

        return response()->json([
            'product'   => $product,
            'from'      => $from?->toDateString(),
            'to'        => $to?->toDateString(),
            'movements' => $movements,
        ]);
    }

}
