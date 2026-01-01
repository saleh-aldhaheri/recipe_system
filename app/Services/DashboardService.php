<?php

namespace App\Services;

use App\Enums\TransactionTypeEnum;
use App\Models\Item;
use App\Models\Transaction;
use DateTime;
use Psr\SimpleCache\CacheInterface;

class DashboardService
{
    public function __construct(
        private CacheInterface $cache
    ) {}

    /**
     * Get items summary for dashboard table
     */
    public function getItemsSummary(DateTime $from, DateTime $to)
    {
        $fromStr = $from->format('Y-m-d');
        $toStr = $to->format('Y-m-d');
        $key = "dashboard_items_summary_{$fromStr}_{$toStr}";
        $result = $this->cache->get($key);

        if ($result !== null) {
            return $result;
        }

        $items = Item::all();
        $itemsData = [];

        foreach ($items as $item) {
            $transactions = Transaction::where('item_id', $item->id)
                ->whereBetween('created_at', [$from, $to])
                ->where('type', TransactionTypeEnum::RECIPE_USAGE->value)
                ->get();

            $total = $transactions->where('operation', '-')->sum('qty_used');

            $itemsData[] = [
                'id' => $item->id,
                'name' => $item->name,
                'short_name' => $item->short_name,
                'total' => (float) $total,
                'balance' => (float) $item->balance,
                'unit' => $item->unit,
            ];
        }

        $result = ['items' => $itemsData];
        $this->cache->set($key, $result, 600); // 10 دقائق

        return $result;
    }

    /**
     * Get dashboard statistics (widgets)
     */
    public function getDashboardStats(DateTime $from, DateTime $to)
    {
        $fromStr = $from->format('Y-m-d');
        $toStr = $to->format('Y-m-d');
        $key = "dashboard_stats_{$fromStr}_{$toStr}";
        $result = $this->cache->get($key);

        if ($result !== null) {
            return $result;
        }

        $query = Transaction::whereBetween('created_at', [$from, $to]);

        $stats = [
            'total_usage' => (float) (clone $query)
                ->where('type', TransactionTypeEnum::RECIPE_USAGE->value)
                ->where('operation', '-')
                ->sum('qty_used'),

            'total_additions' => (float) (clone $query)
                ->where('operation', '+')
                ->sum('qty_used'),

            'total_deductions' => (float) (clone $query)
                ->where('operation', '-')
                ->sum('qty_used'),

            'transactions_count' => (clone $query)->count(),

            'recipes_count' => (clone $query)
                ->where('type', TransactionTypeEnum::RECIPE_USAGE->value)
                ->whereNotNull('recipe_id')
                ->distinct('recipe_id')
                ->count('recipe_id'),

            'active_items_count' => (clone $query)
                ->distinct('item_id')
                ->count('item_id'),

            'total_balance' => (float) Item::sum('balance'),
        ];

        $this->cache->set($key, $stats, 600);

        return $stats;
    }

    /**
     * Get transactions distribution by type (for Pie Chart)
     */
    public function getTransactionsByType(DateTime $from, DateTime $to)
    {
        $fromStr = $from->format('Y-m-d');
        $toStr = $to->format('Y-m-d');
        $key = "dashboard_transactions_by_type_{$fromStr}_{$toStr}";
        $result = $this->cache->get($key);

        if ($result !== null) {
            return $result;
        }

        $query = Transaction::whereBetween('created_at', [$from, $to]);

        $result = [
            'recipe_usage' => (clone $query)
                ->where('type', TransactionTypeEnum::RECIPE_USAGE->value)
                ->count(),
            'update_item' => (clone $query)
                ->where('type', TransactionTypeEnum::UPDATE_ITEM->value)
                ->count(),
        ];

        $this->cache->set($key, $result, 600);

        return $result;
    }

    /**
     * Get top items by usage (for Bar Chart)
     */
    public function getTopItemsByUsage(DateTime $from, DateTime $to, int $limit = 10)
    {
        $fromStr = $from->format('Y-m-d');
        $toStr = $to->format('Y-m-d');
        $key = "dashboard_top_items_{$fromStr}_{$toStr}_{$limit}";
        $result = $this->cache->get($key);

        if ($result !== null) {
            return $result;
        }

        $topItems = Transaction::whereBetween('created_at', [$from, $to])
            ->where('type', TransactionTypeEnum::RECIPE_USAGE->value)
            ->where('operation', '-')
            ->selectRaw('item_id, SUM(qty_used) as total_used')
            ->with('item:id,name,short_name')
            ->groupBy('item_id')
            ->orderByDesc('total_used')
            ->limit($limit)
            ->get()
            ->map(function ($transaction) {
                return [
                    'item_name' => $transaction->item->name,
                    'short_name' => $transaction->item->short_name,
                    'total_used' => (float) $transaction->total_used,
                ];
            });

        $result = ['items' => $topItems];
        $this->cache->set($key, $result, 600);

        return $result;
    }

    /**
     * Get daily usage trend (for Line Chart)
     */
    public function getDailyUsageTrend(DateTime $from, DateTime $to)
    {
        $fromStr = $from->format('Y-m-d');
        $toStr = $to->format('Y-m-d');
        $key = "dashboard_daily_trend_{$fromStr}_{$toStr}";
        $result = $this->cache->get($key);

        if ($result !== null) {
            return $result;
        }

        $dailyData = Transaction::whereBetween('created_at', [$from, $to])
            ->where('type', TransactionTypeEnum::RECIPE_USAGE->value)
            ->where('operation', '-')
            ->selectRaw('DATE(created_at) as date, SUM(qty_used) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total' => (float) $item->total,
                ];
            });

        $result = ['daily_data' => $dailyData];
        $this->cache->set($key, $result, 600);

        return $result;
    }

    /**
     * Get usage by recipes (for Histogram/Bar Chart)
     */
    public function getUsageByRecipes(DateTime $from, DateTime $to, int $limit = 10)
    {
        $fromStr = $from->format('Y-m-d');
        $toStr = $to->format('Y-m-d');
        $key = "dashboard_usage_by_recipes_{$fromStr}_{$toStr}_{$limit}";
        $result = $this->cache->get($key);

        if ($result !== null) {
            return $result;
        }

        $recipesData = Transaction::whereBetween('created_at', [$from, $to])
            ->where('type', TransactionTypeEnum::RECIPE_USAGE->value)
            ->whereNotNull('recipe_id')
            ->selectRaw('recipe_id, SUM(qty_used) as total_used')
            ->with('recipe:id,name')
            ->groupBy('recipe_id')
            ->orderByDesc('total_used')
            ->limit($limit)
            ->get()
            ->map(function ($transaction) {
                return [
                    'recipe_name' => $transaction->recipe->name,
                    'total_used' => (float) $transaction->total_used,
                ];
            });

        $result = ['recipes' => $recipesData];
        $this->cache->set($key, $result, 600);

        return $result;
    }

    /**
     * Get all recipes for a specific item
     */
    public function getItemRecipes(int $itemId, DateTime $from, DateTime $to)
    {
        $key = "dashboard_item_recipes_{$itemId}_{$from->format('Y-m-d')}_{$to->format('Y-m-d')}";
        $result = $this->cache->get($key);

        if ($result !== null) {
            return $result;
        }

        $recipes = Transaction::where('item_id', $itemId)
            ->whereBetween('created_at', [$from, $to])
            ->where('type', TransactionTypeEnum::RECIPE_USAGE->value)
            ->whereNotNull('recipe_id')
            ->with('recipe:id,name,date')
            ->selectRaw('recipe_id, SUM(qty_used) as total_used, MAX(created_at) as last_used')
            ->groupBy('recipe_id')
            ->orderByDesc('total_used')
            ->get()
            ->map(function ($transaction) {
                return [
                    'recipe_id' => $transaction->recipe_id,
                    'recipe_name' => $transaction->recipe->name,
                    'recipe_date' => $transaction->recipe->date,
                    'total_used' => (float) $transaction->total_used,
                    'last_used' => $transaction->last_used,
                ];
            });

        $result = ['recipes' => $recipes];
        $this->cache->set($key, $result, 600);

        return $result;
    }
}
