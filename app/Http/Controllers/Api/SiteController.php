<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Sites fetched successfully.',
            'data' => Site::latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => ['required', 'string', 'max:100', 'unique:sites,site_id'],
            'site_name' => ['required', 'string', 'max:255'],
        ]);

        $site = Site::create([
            'site_id' => trim($validated['site_id']),
            'site_name' => trim($validated['site_name']),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Site created successfully.',
            'data' => $site,
        ], 201);
    }

    public function destroy(Site $site): JsonResponse
    {
        $site->delete();

        return response()->json([
            'status' => true,
            'message' => 'Site deleted successfully.',
        ]);
    }
}
