<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Sale;

class AccountingService
{
    private function getAccount(int $companyId, string $code): Account
    {
        return Account::where('company_id', $companyId)
            ->where('code', $code)
            ->firstOrFail();
    }

    public function recordSale(Sale $sale, float $cost): JournalEntry
    {
        $companyId = $sale->company_id;

        $entry = JournalEntry::create([
            'company_id'      => $companyId,
            'user_id'         => $sale->user_id,
            'reference'       => $sale->invoice_number,
            'description'     => "فاتورة مبيعات #{$sale->invoice_number}",
            'date'            => now()->toDateString(),
            'type'            => 'sale',
            'sourceable_id'   => $sale->id,
            'sourceable_type' => Sale::class,
        ]);

        $lines = [];

        if ($sale->paid > 0) {
            $cashAccount = $sale->payment_method === 'cash'
                ? $this->getAccount($companyId, '1010')
                : $this->getAccount($companyId, '1020');

            $lines[] = [
                'account_id'  => $cashAccount->id,
                'debit'       => $sale->paid,
                'credit'      => 0,
                'description' => 'المبلغ المدفوع',
            ];
        }

        if ($sale->balance > 0) {
            $receivable = $this->getAccount($companyId, '1100');
            $lines[] = [
                'account_id'  => $receivable->id,
                'debit'       => $sale->balance,
                'credit'      => 0,
                'description' => 'المبلغ الآجل',
            ];
        }

        $salesAccount = $this->getAccount($companyId, '4000');
        $lines[] = [
            'account_id'  => $salesAccount->id,
            'debit'       => 0,
            'credit'      => $sale->total,
            'description' => 'إيراد المبيعات',
        ];

        if ($cost > 0) {
            $cogsAccount      = $this->getAccount($companyId, '5000');
            $inventoryAccount = $this->getAccount($companyId, '1300');

            $lines[] = [
                'account_id'  => $cogsAccount->id,
                'debit'       => $cost,
                'credit'      => 0,
                'description' => 'تكلفة البضاعة المباعة',
            ];
            $lines[] = [
                'account_id'  => $inventoryAccount->id,
                'debit'       => 0,
                'credit'      => $cost,
                'description' => 'تخفيض المخزون',
            ];
        }

        $entry->lines()->createMany($lines);

        return $entry;
    }

    public function getProfitLoss(int $companyId, string $from, string $to): array
    {
        $revenue  = $this->sumAccountType($companyId, 'revenue', $from, $to);
        $expenses = $this->sumAccountType($companyId, 'expense', $from, $to);
        $profit   = $revenue - $expenses;

        return [
            'revenue'  => $revenue,
            'expenses' => $expenses,
            'profit'   => $profit,
            'status'   => $profit >= 0 ? 'ربح' : 'خسارة',
        ];
    }

    private function sumAccountType(int $companyId, string $type, string $from, string $to): float
    {
        return Account::where('company_id', $companyId)
            ->where('type', $type)
            ->get()
            ->sum(fn ($account) => $account->journalLines()
                ->whereHas('journalEntry', fn ($q) =>
                    $q->whereBetween('date', [$from, $to])
                )
                ->selectRaw('SUM(credit) - SUM(debit) as balance')
                ->value('balance') ?? 0
            );
    }
}
