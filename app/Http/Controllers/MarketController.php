<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Product;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function show_all_markets()
    {
        $markets = Market::with('image')->get();
        return response()->json([
            'status' => 200,
            'message' => 'Markets retrieved successfully',
            'data' => $markets,
        ]);
    }
    public function search_market(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);
        $name_Market = $request->name;

        $Market = Market::where('name', 'like', '%' . $name_Market . '%')->with('image')->get();
        if ($Market->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No products found',
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Products retrieved successfully',
            'data' => $Market,
        ]);
    }
}
