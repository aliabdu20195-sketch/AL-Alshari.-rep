<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        private AccountingService $accounting,
        private InventoryService $inventory
    ) {}

    public function create(array $data, int $companyId, int $userId): Sale
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {

            $subtotal  = 0;
            $totalCost = 0;
            $itemsToCreate = [];

            foreach ($data['items'] as $item) {
                $product = Product::where('company_id', $companyId)
                    ->where(function ($q) use ($item) {
                        $q->where('barcode', $item['barcode'])
                          ->orWhere('id', $item['barcode']);
                    })
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock < $item['qty']) {
                    throw ValidationException::withMessages([
                        'items' => "المخزون غير كافٍ للمنتج: {$product->name}. المتاح: {$product->stock}"
                    ]);
                }

                $lineTotal = $product->price * $item['qty'];
                $lineCost  = $product->cost  * $item['qty'];

                $subtotal  += $lineTotal;
                $totalCost += $lineCost;

                $itemsToCreate[] = [
                    'product'  => $product,
                    'qty'      => $item['qty'],
                    'price'    => $product->price,
                    'cost'     => $product->cost,
                    'discount' => 0,
                    'total'    => $lineTotal,
                ];
            }

            $discount = $data['discount'] ?? 0;
            $tax      = ($subtotal - $discount) * ($data['tax_rate'] ?? 0);
            $total    = $subtotal - $discount + $tax;
            $paid     = $data['paid'];
            $balance  = $total - $paid;

            $sale = Sale::create([
                'company_id'     => $companyId,
                'user_id'        => $userId,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'paid'           => $paid,
                'balance'        => $balance,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'status'         => 'completed',
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($itemsToCreate as $item) {
                $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'qty'        => $item['qty'],
                    'price'      => $item['price'],
                    'cost'       => $item['cost'],
                    'discount'   => $item['discount'],
                    'total'      => $item['total'],
                ]);

                $this->inventory->decrease($item['product'], $item['qty']);
            }

            $this->accounting->recordSale($sale, $totalCost);

            return $sale->load('items.product');
        });
    }
}
