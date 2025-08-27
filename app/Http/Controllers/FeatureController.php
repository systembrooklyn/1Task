<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feature;

class FeatureController extends Controller
{
    public function index()
    {
        $features = Feature::get();
        return response()->json([
            'message' => 'Features retreived successfully',
            'data' => $features
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:features,slug',
            'unit' => 'required|string|in:count,kb,mb',
            'reset_frequency' => 'nullable|string|in:daily,weekly,monthly',
        ]);

        $feature = Feature::create($request->only(['name', 'slug', 'unit', 'reset_frequency']));

        return response()->json([
            'message' => 'Feature Created Successfully',
            'data' => $feature
        ], 201);
    }
}
