<?php

namespace App\Services\AI;

use App\Models\Sale;
use App\Models\Product;
use App\Services\InventoryService;

class SmartERP
{
    public function __construct(private InventoryService $inventory) {}

    public function forecastSales(int $companyId, int $days = 30): array
    {
        $sales = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days * 2))
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();

        if (count($sales) < 2) {
            return ['forecast' => 0, 'trend' => 'بيانات غير كافية', 'confidence' => 'منخفضة'];
        }

        $alpha = 0.3;
        $ema   = $sales[0];
        foreach (array_slice($sales, 1) as $value) {
            $ema = $alpha * $value + (1 - $alpha) * $ema;
        }

        $avg   = array_sum($sales) / count($sales);
        $trend = $ema > $avg ? 'تصاعدي' : ($ema < $avg ? 'تنازلي' : 'مستقر');

        return [
            'forecast_next_day' => round($ema, 2),
            'average_daily'     => round($avg, 2),
            'trend'             => $trend,
            'confidence'        => count($sales) > 14 ? 'عالية' : 'متوسطة',
            'data_points'       => count($sales),
        ];
    }

    public function inventoryAlerts(int $companyId): array
    {
        $lowStock = $this->inventory->getLowStockProducts($companyId);

        return $lowStock->map(fn ($p) => [
            'id'     => $p->id,
            'name'   => $p->name,
            'stock'  => $p->stock,
            'min'    => $p->min_stock,
            'status' => $p->stockStatus(),
            'action' => $p->stock <= 0
                ? 'أوقف البيع فوراً'
                : "اطلب على الأقل " . ($p->min_stock * 3 - $p->stock) . " وحدة",
        ])->toArray();
    }

    public function profitAnalysis(int $companyId): array
    {
        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->get()
            ->map(fn ($p) => [
                'id'     => $p->id,
                'name'   => $p->name,
                'price'  => $p->price,
                'cost'   => $p->cost,
                'margin' => $p->profitMargin(),
                'status' => match(true) {
                    $p->profitMargin() < 0  => 'خسارة',
                    $p->profitMargin() < 10 => 'ضعيف',
                    $p->profitMargin() < 30 => 'مقبول',
                    default                  => 'ممتاز',
                },
            ])
            ->sortByDesc('margin')
            ->values();

        return [
            'products'   => $products->toArray(),
            'best'       => $products->first(),
            'worst'      => $products->last(),
            'avg_margin' => round($products->avg('margin'), 2),
        ];
    }

    public function dashboard(int $companyId): array
    {
        $todaySales = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('total');

        $monthSales = Sale::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $lowStockCount = Product::where('company_id', $companyId)
            ->whereColumn('stock', '<', 'min_stock')
            ->count();

        return [
            'today_sales'     => $todaySales,
            'month_sales'     => $monthSales,
            'low_stock_count' => $lowStockCount,
            'forecast'        => $this->forecastSales($companyId),
            'alerts'          => $this->inventoryAlerts($companyId),
        ];
    }
}
