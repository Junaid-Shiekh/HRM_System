<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetIssuance extends Model
{
    use HasFactory, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'inventory_item_id',
        'warehouse_id',
        'employee_id',
        'quantity',
        'issued_date',
        'returned_date',
        'status',
        'notes',
        'created_by',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
