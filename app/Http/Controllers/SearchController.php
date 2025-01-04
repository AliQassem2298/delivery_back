<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Market;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            $name = $request->name;

            // البحث في الأسواق
            $markets = Market::where('name', 'like', '%' . $name . '%')->with('image')->get();

            // البحث في المنتجات
            $products = Product::where('name', 'like', '%' . $name . '%')->with('image')->get();

            // التحقق من وجود نتائج
            if ($markets->isEmpty() && $products->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No results found for the provided name.',
                ], 404);
            }

            // إعادة النتائج في JSON
            return response()->json([
                'status' => 200,
                'message' => 'Results retrieved successfully.',
                'data' => [
                    'markets' => $markets,
                    'products' => $products,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during the search process.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
