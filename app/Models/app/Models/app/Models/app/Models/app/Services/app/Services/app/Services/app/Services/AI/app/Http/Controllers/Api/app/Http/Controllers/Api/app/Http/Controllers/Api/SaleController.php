<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private SaleService $saleService) {}

    public function index(Request $request): JsonResponse
    {
        $sales = Sale::where('company_id', $request->user()->company_id)
            ->with('items.product', 'user')
            ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to,   fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest()
            ->paginate(20);

        return response()->json($sales);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.barcode' => 'required|string',
            'items.*.qty'     => 'required|integer|min:1',
            'paid'            => 'required|numeric|min:0',
            'discount'        => 'nullable|numeric|min:0',
            'tax_rate'        => 'nullable|numeric|min:0|max:1',
            'payment_method'  => 'nullable|in:cash,card,transfer,credit',
            'notes'           => 'nullable|string',
        ]);

        $sale = $this->saleService->create(
            $data,
            $request->user()->company_id,
            $request->user()->id
        );

        return response()->json($sale, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $sale = Sale::where('company_id', $request->user()->company_id)
            ->with('items.product', 'user', 'journalEntry.lines.account')
            ->findOrFail($id);

        return response()->json($sale);
    }
}
