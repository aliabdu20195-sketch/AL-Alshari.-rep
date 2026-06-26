<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'reference', 'description',
        'date', 'type', 'sourceable_id', 'sourceable_type'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isBalanced(): bool
    {
        $totalDebit  = $this->lines()->sum('debit');
        $totalCredit = $this->lines()->sum('credit');
        return abs($totalDebit - $totalCredit) < 0.01;
    }
}
