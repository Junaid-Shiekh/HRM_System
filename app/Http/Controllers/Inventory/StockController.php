<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\StockLog;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/Stocks/Index', [
            'stocks' => Stock::with(['item.category', 'warehouse'])->get(),
            'items' => InventoryItem::all(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function logs()
    {
        return Inertia::render('Inventory/Stocks/Logs', [
            'logs' => StockLog::with(['item', 'warehouse', 'user'])->latest()->get()
        ]);
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric', // Delta quantity
            'reason' => 'required|string',
        ]);

        DB::transaction(function () use ($validated) {
            $stock = Stock::firstOrCreate(
                ['inventory_item_id' => $validated['inventory_item_id'], 'warehouse_id' => $validated['warehouse_id']],
                ['quantity' => 0]
            );

            $stock->increment('quantity', $validated['quantity']);

            StockLog::create([
                'inventory_item_id' => $validated['inventory_item_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'quantity' => $validated['quantity'],
                'type' => 'Adjustment',
                'reason' => $validated['reason'],
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }
}
