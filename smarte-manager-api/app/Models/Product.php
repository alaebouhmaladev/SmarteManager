<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'current_stock',
        'min_stock',
        'average_cost',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
