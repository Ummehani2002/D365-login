<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Sizes fetched successfully.',
            'data' => Size::latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'd365_size_name' => ['required', 'string', 'max:100'],
        ]);

        $size = Size::updateOrCreate(
            ['d365_size_name' => trim($validated['d365_size_name'])],
            []
        );

        $wasNew = $size->wasRecentlyCreated;

        if ($wasNew && auth()->check()) {
            $size->forceFill(['created_by' => auth()->id()])->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Size saved successfully.',
            'data' => $size->fresh(),
        ], $wasNew ? 201 : 200);
    }

    public function destroy(Size $size): JsonResponse
    {
        $size->delete();

        return response()->json([
            'status' => true,
            'message' => 'Size deleted successfully.',
        ]);
    }
}
