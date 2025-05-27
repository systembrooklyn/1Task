<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:promo_codes',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
            'max_uses' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $promo = PromoCode::create($validated);
        $promo->plans()->attach($request->input('plan_ids', []));

        return response()->json($promo, 201);
    }

    public function index()
    {
        return PromoCode::with('plans')->get();
    }
}