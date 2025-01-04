<?php

namespace App\Http\Controllers;
use App\Models\Amount;
use Illuminate\Http\Request;

class AmountController extends Controller
{
    public function getProductAmounts($product_id)
    {
        $amounts = Amount::with('market')
            ->where('product_id', $product_id)
            ->get();
        if ($amounts->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No amounts found for this product',
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Amounts retrieved successfully',
            'data' => $amounts,
        ]);
    }
    public function checkStock($productId, $marketId, $requiredQuantity)
    {
        $amount = Amount::where('product_id', $productId)
            ->where('market_id', $marketId)
            ->first();

        if (!$amount) {
            return [
                'status' => false,
                'message' => 'Product not available in this market',
            ];
        }
        if ($amount->amount < $requiredQuantity) {
            return [
                'status' => false,
                'message' => 'Insufficient stock for this product in the selected market',
            ];
        }
        return [
            'status' => true,
            'message' => 'Stock is sufficient',
        ];
    }

}
