<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\SmartERP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(private SmartERP $ai) {}

    public function dashboard(Request $request): JsonResponse
    {
        return response()->json(
            $this->ai->dashboard($request->user()->company_id)
        );
    }

    public function forecast(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        return response()->json(
            $this->ai->forecastSales($request->user()->company_id, $days)
        );
    }

    public function inventoryAlerts(Request $request): JsonResponse
    {
        return response()->json(
            $this->ai->inventoryAlerts($request->user()->company_id)
        );
    }

    public function profitAnalysis(Request $request): JsonResponse
    {
        return response()->json(
            $this->ai->profitAnalysis($request->user()->company_id)
        );
    }
}
