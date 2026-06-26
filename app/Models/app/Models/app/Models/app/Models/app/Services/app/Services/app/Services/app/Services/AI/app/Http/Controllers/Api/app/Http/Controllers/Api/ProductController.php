<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::where('company_id', $request->user()->company_id)
            ->when($request->search, fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%")
            )
            ->when($request->low_stock, fn ($q) =>
                $q->whereColumn('stock', '<', 'min_stock')
            )
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'barcode'   => 'nullable|string',
            'name'      => 'required|string|max:255',
            'cost'      => 'required|numeric|min:0',
            'price'     => 'required|numeric|min:0',
            'stock'     => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit'      => 'nullable|string',
        ]);

        $data['company_id'] = $request->user()->company_id;

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $product = Product::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        return response()->json($product);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        $data = $request->validate([
            'barcode'   => 'nullable|string',
            'name'      => 'sometimes|string|max:255',
            'cost'      => 'sometimes|numeric|min:0',
            'price'     => 'sometimes|numeric|min:0',
            'stock'     => 'sometimes|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
            'unit'      => 'nullable|string',
        ]);

        $product->update($data);

        return response()->json($product);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = Product::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        $product->update(['is_active' => false]);

        return response()->json(['message' => 'تم حذف المنتج']);
    }
}
