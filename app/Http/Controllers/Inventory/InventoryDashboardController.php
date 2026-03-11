<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\JobPosting;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        $lowStockCount = InventoryItem::whereHas('stocks', function ($query) {
            $query->select(DB::raw('sum(quantity) as total_qty'))
                  ->groupBy('inventory_item_id')
                  ->havingRaw('total_qty <= inventory_items.reorder_point');
        })->count();

        return Inertia::render('Inventory/Dashboard', [
            'stats' => [
                'totalItems' => InventoryItem::count(),
                'totalSuppliers' => Supplier::count(),
                'totalWarehouses' => Warehouse::count(),
                'pendingPOs' => PurchaseOrder::where('status', 'Pending')->count(),
                'lowStockItems' => $lowStockCount,
            ],
            'recentLogs' => StockLog::with(['item', 'warehouse'])->latest()->take(5)->get(),
            'warehouseStocks' => Warehouse::with(['stocks' => function($q) {
                $q->with('item');
            }])->get(),
        ]);
    }

    public function forecast()
    {
        // 1. Historical Data Analysis (Last 30 days for daily rate)
        $historicalUsage = StockLog::where('type', 'Out')
            ->where('created_at', '>=', now()->subDays(60)) // Look back 60 days for better average
            ->select('inventory_item_id', DB::raw('ABS(SUM(quantity)) as total_used'))
            ->groupBy('inventory_item_id')
            ->get()
            ->keyBy('inventory_item_id');

        // 2. HR Hiring Data Integration
        $openJobPositions = JobPosting::where('status', 'published') 
            ->count();

        // 3. Prediction & Alert Logic
        $items = InventoryItem::with(['stocks', 'category'])->get();
        
        $forecastReports = $items->map(function ($item) use ($historicalUsage, $openJobPositions) {
            $totalUsed60Days = $historicalUsage->has($item->id) ? $historicalUsage[$item->id]->total_used : 0;
            $dailyUsageRate = $totalUsed60Days / 60;
            $currentStock = $item->stocks->sum('quantity');
            
            // HR Linked Demand: Items needed for new onboardings
            $hrDemand = 0;
            $keywords = ['laptop', 'headset', 'chair', 'uniform', 'notebook', 'id card', 'keyboard', 'mouse', 'desk'];
            foreach ($keywords as $keyword) {
                if (stripos($item->name, $keyword) !== false) {
                    $hrDemand = $openJobPositions;
                    break;
                }
            }
            
            // Days Until Empty
            $daysUntilEmpty = $dailyUsageRate > 0 ? floor($currentStock / $dailyUsageRate) : 999;
            
            // Suggested Reorder Logic
            // Formula: (Usage for next 30 days + HR Demand) - Current Stock + Buffer
            $predicted30DayUsage = $dailyUsageRate * 30;
            $totalPredictedDemand = $predicted30DayUsage + $hrDemand;
            
            $suggestedReorder = 0;
            $suggestedOrderDate = null;
            $leadTimeDays = 7; // Average procurement lead time

            if ($daysUntilEmpty <= $leadTimeDays + 5 || ($currentStock - $hrDemand) <= $item->reorder_point) {
                $suggestedReorder = max($item->min_stock_level, $totalPredictedDemand * 1.2); // 20% buffer
                
                $orderInDays = max(0, $daysUntilEmpty - $leadTimeDays);
                $suggestedOrderDate = now()->addDays($orderInDays)->format('Y-m-d');
            }

            return [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'category' => $item->category->name,
                'current_stock' => $currentStock . ' ' . $item->uom,
                'daily_usage' => round($dailyUsageRate, 2) . ' / day',
                'days_until_empty' => $daysUntilEmpty > 365 ? '365+' : $daysUntilEmpty . ' days',
                'hr_linked_demand' => $hrDemand,
                'suggested_reorder_qty' => round($suggestedReorder, 0) . ' ' . $item->uom,
                'suggested_order_date' => $suggestedOrderDate ? Carbon::parse($suggestedOrderDate)->format('M d, Y') : 'Stock Sufficient',
                'status' => $daysUntilEmpty <= 7 ? 'Critical' : ($daysUntilEmpty <= 15 ? 'Low' : 'Healthy'),
            ];
        });

        return Inertia::render('Inventory/ForecastReport', [
            'forecastData' => $forecastReports,
            'hiringContext' => [
                'openPositions' => $openJobPositions,
            ]
        ]);
    }
}
