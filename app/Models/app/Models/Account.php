<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'company_id', 'code', 'name', 'type', 'normal_balance', 'is_active'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function balance(): float
    {
        $debit  = $this->journalLines()->sum('debit');
        $credit = $this->journalLines()->sum('credit');

        return $this->normal_balance === 'debit'
            ? $debit - $credit
            : $credit - $debit;
    }
}
