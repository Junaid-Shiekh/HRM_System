<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryItemController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/Items/Index', [
            'items' => InventoryItem::with(['category', 'stocks.warehouse'])->latest()->get(),
            'categories' => InventoryCategory::all(),
            'suppliers' => Supplier::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'item_code' => 'required|string|unique:inventory_items,item_code',
            'name' => 'required|string|max:255',
            'uom' => 'required|string|max:50',
            'min_stock_level' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        $item = InventoryItem::create($validated);

        if ($request->has('supplier_ids')) {
            $item->suppliers()->sync($request->supplier_ids);
        }

        return redirect()->back()->with('success', 'Item created successfully.');
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'item_code' => 'required|string|unique:inventory_items,item_code,' . $item->id,
            'name' => 'required|string|max:255',
            'uom' => 'required|string|max:50',
            'min_stock_level' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        $item->update($validated);

        if ($request->has('supplier_ids')) {
            $item->suppliers()->sync($request->supplier_ids);
        }

        return redirect()->back()->with('success', 'Item updated successfully.');
    }

    public function destroy(InventoryItem $item)
    {
        if ($item->stocks()->where('quantity', '>', 0)->exists()) {
            return redirect()->back()->with('error', 'Item cannot be deleted as it has remaining stock.');
        }

        $item->delete();

        return redirect()->back()->with('success', 'Item deleted successfully.');
    }
}
