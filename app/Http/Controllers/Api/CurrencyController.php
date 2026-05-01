<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Currencies fetched successfully.',
            'data' => Currency::latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'currency_code' => ['required', 'string', 'max:20', 'unique:currencies,currency_code'],
            'txt' => ['required', 'string', 'max:255'],
        ]);

        $currency = Currency::create([
            'currency_code' => strtoupper(trim($validated['currency_code'])),
            'txt' => trim($validated['txt']),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Currency created successfully.',
            'data' => $currency,
        ], 201);
    }

    public function destroy(Currency $currency): JsonResponse
    {
        $currency->delete();

        return response()->json([
            'status' => true,
            'message' => 'Currency deleted successfully.',
        ]);
    }
}
