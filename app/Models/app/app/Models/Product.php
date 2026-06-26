<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'company_id', 'barcode', 'name', 'description',
        'cost', 'price', 'stock', 'min_stock', 'unit', 'is_active'
    ];

    protected $casts = [
        'cost'      => 'float',
        'price'     => 'float',
        'stock'     => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockStatus(): string
    {
        if ($this->stock <= 0)               return 'نفد';
        if ($this->stock < $this->min_stock) return 'حرج';
        if ($this->stock < $this->min_stock * 2) return 'تنبيه';
        return 'طبيعي';
    }

    public function profitMargin(): float
    {
        if ($this->price <= 0) return 0;
        return round((($this->price - $this->cost) / $this->price) * 100, 2);
    }
}
