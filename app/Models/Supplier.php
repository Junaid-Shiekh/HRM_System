<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'supply_details',
        'rating',
    ];

    public function inventoryItems()
    {
        return $this->belongsToMany(InventoryItem::class, 'inventory_item_supplier')
                    ->withPivot('last_purchase_price')
                    ->withTimestamps();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
