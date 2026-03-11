<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\AssetIssuance;
use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\StockLog;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssetIssuanceController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/AssetIssuances/Index', [
            'issuances' => AssetIssuance::with(['item', 'warehouse', 'employee', 'creator'])->latest()->get(),
            'employees' => Employee::all(),
            'warehouses' => Warehouse::all(),
            'items' => InventoryItem::with('stocks')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'employee_id' => 'required|exists:employees,id',
            'quantity' => 'required|numeric|min:0.01',
            'issued_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            // Check stock
            $stock = Stock::where('warehouse_id', $validated['warehouse_id'])
                          ->where('inventory_item_id', $validated['inventory_item_id'])
                          ->first();

            if (!$stock || $stock->quantity < $validated['quantity']) {
                throw new \Exception("Insufficient stock in warehouse.");
            }

            // Create issuance record
            $issuance = AssetIssuance::create([
                'inventory_item_id' => $validated['inventory_item_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'employee_id' => $validated['employee_id'],
                'quantity' => $validated['quantity'],
                'issued_date' => $validated['issued_date'],
                'status' => 'Issued',
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            // Deduct stock
            $stock->decrement('quantity', $validated['quantity']);

            // Log movement
            StockLog::create([
                'inventory_item_id' => $validated['inventory_item_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'quantity' => -$validated['quantity'],
                'type' => 'Out',
                'reference_type' => 'AssetIssuance',
                'reference_id' => $issuance->id,
                'reason' => 'Issued to Employee ID: ' . $validated['employee_id'],
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Asset issued to employee.');
    }

    public function returnAsset(Request $request, AssetIssuance $assetIssuance)
    {
        if ($assetIssuance->status !== 'Issued') {
            return redirect()->back()->with('error', 'Asset is not in issued status.');
        }

        $validated = $request->validate([
            'returned_date' => 'required|date',
            'status' => 'required|in:Returned,Lost,Damaged',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $assetIssuance) {
            $assetIssuance->update([
                'returned_date' => $validated['returned_date'],
                'status' => $validated['status'],
                'notes' => $assetIssuance->notes . "\nReturn Notes: " . $validated['notes'],
            ]);

            if ($validated['status'] === 'Returned') {
                // Return to stock
                $stock = Stock::firstOrCreate(
                    ['warehouse_id' => $assetIssuance->warehouse_id, 'inventory_item_id' => $assetIssuance->inventory_item_id],
                    ['quantity' => 0]
                );
                $stock->increment('quantity', $assetIssuance->quantity);

                // Log movement
                StockLog::create([
                    'inventory_item_id' => $assetIssuance->inventory_item_id,
                    'warehouse_id' => $assetIssuance->warehouse_id,
                    'quantity' => $assetIssuance->quantity,
                    'type' => 'In',
                    'reference_type' => 'AssetIssuance',
                    'reference_id' => $assetIssuance->id,
                    'reason' => 'Returned by Employee ID: ' . $assetIssuance->employee_id,
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Asset return processed.');
    }
}
