<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/PurchaseOrders/Index', [
            'purchaseOrders' => PurchaseOrder::with(['supplier', 'warehouse', 'creator', 'items.item'])->latest()->get(),
            'suppliers' => Supplier::all(),
            'warehouses' => Warehouse::all(),
            'items' => InventoryItem::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $poCount = PurchaseOrder::count() + 1;
            $poNumber = 'PO-' . str_pad($poCount, 6, '0', STR_PAD_LEFT);

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'notes' => $validated['notes'],
                'status' => 'Pending',
                'created_by' => auth()->id(),
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            foreach ($validated['items'] as $itemData) {
                $totalItemCost = $itemData['quantity'] * $itemData['unit_cost'];
                $totalAmount += $totalItemCost;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'total_cost' => $totalItemCost,
                ]);
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);
        });

        return redirect()->back()->with('success', 'Purchase Order created successfully.');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending orders can be approved.');
        }

        $purchaseOrder->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Purchase Order approved.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'Approved') {
            return redirect()->back()->with('error', 'Only approved orders can be received.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $purchaseOrder) {
            foreach ($validated['items'] as $itemId => $data) {
                $poItem = PurchaseOrderItem::findOrFail($itemId);
                $poItem->update(['received_quantity' => $data['received_quantity']]);

                if ($data['received_quantity'] > 0) {
                    // Update Stock
                    $stock = Stock::firstOrCreate(
                        ['inventory_item_id' => $poItem->inventory_item_id, 'warehouse_id' => $purchaseOrder->warehouse_id],
                        ['quantity' => 0]
                    );

                    $stock->increment('quantity', $data['received_quantity']);

                    // Log Stock Movement
                    StockLog::create([
                        'inventory_item_id' => $poItem->inventory_item_id,
                        'warehouse_id' => $purchaseOrder->warehouse_id,
                        'quantity' => $data['received_quantity'],
                        'type' => 'In',
                        'reference_type' => 'PurchaseOrder',
                        'reference_id' => $purchaseOrder->id,
                        'reason' => 'PO Received: ' . $purchaseOrder->po_number,
                        'user_id' => auth()->id(),
                    ]);
                    
                    // Update Supplier's last purchase price for this item
                    DB::table('inventory_item_supplier')
                        ->updateOrInsert(
                            ['inventory_item_id' => $poItem->inventory_item_id, 'supplier_id' => $purchaseOrder->supplier_id],
                            ['last_purchase_price' => $poItem->unit_cost, 'updated_at' => now()]
                        );
                }
            }

            $purchaseOrder->update(['status' => 'Received']);
        });

        return redirect()->back()->with('success', 'Purchase Order items received and stock updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'Received') {
            return redirect()->back()->with('error', 'Received orders cannot be deleted.');
        }

        $purchaseOrder->delete();
        return redirect()->back()->with('success', 'Purchase Order deleted.');
    }
}
