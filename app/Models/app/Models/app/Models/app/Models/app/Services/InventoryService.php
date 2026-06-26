<?php

namespace App\Services;

use App\Models\Product;

class InventoryService
{
    public function decrease(Product $product, int $qty): void
    {
        $product->decrement('stock', $qty);
    }

    public function increase(Product $product, int $qty): void
    {
        $product->increment('stock', $qty);
    }

    public function getLowStockProducts(int $companyId): \Illuminate\Support\Collection
    {
        return Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereColumn('stock', '<', 'min_stock')
            ->orderBy('stock')
            ->get();
    }
}
