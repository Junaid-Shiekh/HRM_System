<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'category_id',
        'item_code',
        'name',
        'uom',
        'min_stock_level',
        'reorder_point',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'inventory_item_supplier')
                    ->withPivot('last_purchase_price')
                    ->withTimestamps();
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function totalStock()
    {
        return $this->stocks()->sum('quantity');
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }
}
