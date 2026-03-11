<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\StockLog;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WarehouseTransferController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/Transfers/Index', [
            'transfers' => WarehouseTransfer::with(['fromWarehouse', 'toWarehouse', 'creator', 'items.item'])->latest()->get(),
            'warehouses' => Warehouse::all(),
            'items' => InventoryItem::with('stocks')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($validated) {
            $transferCount = WarehouseTransfer::count() + 1;
            $transferNumber = 'TR-' . str_pad($transferCount, 6, '0', STR_PAD_LEFT);

            $transfer = WarehouseTransfer::create([
                'transfer_number' => $transferNumber,
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'transfer_date' => $validated['transfer_date'],
                'notes' => $validated['notes'],
                'status' => 'Pending',
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                // Check if source warehouse has enough stock
                $sourceStock = Stock::where('warehouse_id', $validated['from_warehouse_id'])
                                     ->where('inventory_item_id', $itemData['inventory_item_id'])
                                     ->first();

                if (!$sourceStock || $sourceStock->quantity < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock in source warehouse for item ID: " . $itemData['inventory_item_id']);
                }

                WarehouseTransferItem::create([
                    'warehouse_transfer_id' => $transfer->id,
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity' => $itemData['quantity'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Transfer request created.');
    }

    public function complete(WarehouseTransfer $transfer)
    {
        if ($transfer->status !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending transfers can be completed.');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Deduct from source
                $sourceStock = Stock::where('warehouse_id', $transfer->from_warehouse_id)
                                     ->where('inventory_item_id', $item->inventory_item_id)
                                     ->first();
                $sourceStock->decrement('quantity', $item->quantity);

                // Add to destination
                $destStock = Stock::firstOrCreate(
                    ['warehouse_id' => $transfer->to_warehouse_id, 'inventory_item_id' => $item->inventory_item_id],
                    ['quantity' => 0]
                );
                $destStock->increment('quantity', $item->quantity);

                // Log movements
                StockLog::create([
                    'inventory_item_id' => $item->inventory_item_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'quantity' => -$item->quantity,
                    'type' => 'Transfer',
                    'reference_type' => 'WarehouseTransfer',
                    'reference_id' => $transfer->id,
                    'reason' => 'Transfer Out to ' . $transfer->toWarehouse->name,
                    'user_id' => auth()->id(),
                ]);

                StockLog::create([
                    'inventory_item_id' => $item->inventory_item_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'quantity' => $item->quantity,
                    'type' => 'Transfer',
                    'reference_type' => 'WarehouseTransfer',
                    'reference_id' => $transfer->id,
                    'reason' => 'Transfer In from ' . $transfer->fromWarehouse->name,
                    'user_id' => auth()->id(),
                ]);
            }

            $transfer->update(['status' => 'Completed']);
        });

        return redirect()->back()->with('success', 'Transfer completed successfully.');
    }

    public function destroy(WarehouseTransfer $transfer)
    {
        if ($transfer->status === 'Completed') {
            return redirect()->back()->with('error', 'Completed transfers cannot be deleted.');
        }

        $transfer->delete();
        return redirect()->back()->with('success', 'Transfer request deleted.');
    }
}
