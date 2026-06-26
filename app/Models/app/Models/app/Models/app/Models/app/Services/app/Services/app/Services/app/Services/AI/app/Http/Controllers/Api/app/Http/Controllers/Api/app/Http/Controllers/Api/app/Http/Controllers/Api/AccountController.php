<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(private AccountingService $accounting) {}

    public function index(Request $request): JsonResponse
    {
        $accounts = Account::where('company_id', $request->user()->company_id)
            ->orderBy('code')
            ->get()
            ->map(fn ($a) => array_merge($a->toArray(), ['balance' => $a->balance()]));

        return response()->json($accounts);
    }

    public function journal(Request $request): JsonResponse
    {
        $entries = JournalEntry::where('company_id', $request->user()->company_id)
            ->with('lines.account')
            ->when($request->from, fn ($q) => $q->whereDate('date', '>=', $request->from))
            ->when($request->to,   fn ($q) => $q->whereDate('date', '<=', $request->to))
            ->latest('date')
            ->paginate(20);

        return response()->json($entries);
    }

    public function profitLoss(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $result = $this->accounting->getProfitLoss(
            $request->user()->company_id,
            $data['from'],
            $data['to']
        );

        return response()->json($result);
    }

    public function balanceSheet(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $accounts = Account::where('company_id', $companyId)
            ->get()
            ->groupBy('type')
            ->map(fn ($group) => $group->map(fn ($a) => [
                'code'    => $a->code,
                'name'    => $a->name,
                'balance' => $a->balance(),
            ]));

        $totalAssets      = collect($accounts->get('asset', []))->sum('balance');
        $totalLiabilities = collect($accounts->get('liability', []))->sum('balance');
        $totalEquity      = collect($accounts->get('equity', []))->sum('balance');

        return response()->json([
            'assets'      => $accounts->get('asset', []),
            'liabilities' => $accounts->get('liability', []),
            'equity'      => $accounts->get('equity', []),
            'totals' => [
                'assets'             => $totalAssets,
                'liabilities'        => $totalLiabilities,
                'equity'             => $totalEquity,
                'liabilities_equity' => $totalLiabilities + $totalEquity,
                'balanced'           => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
            ],
        ]);
    }
}
