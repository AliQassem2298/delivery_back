<?php

namespace App\Http\Controllers;

use App\Models\Amount;
use App\Models\Market;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    //returns all products with their associated images.
    public function show_all_products()
    {
        $products = Product::with('image')->get();

        return response()->json([
            'status' => 200,
            'message' => 'products retrieved successfully',
            'data' => $products,
        ]);
    }
    //returns products available in a specific market
    public function show_products($market_id)
    {
        try {
            $market = Market::find($market_id);
            if (!$market) {
                return response()->json([
                    'message' => 'market not found', 'status' => 404
                ]);
            }
          $products =$market->products()->with('image')->get()->makeHidden('pivot');
            if ($products->isEmpty()) {
                return response()->json([
                    'message' => 'There are no products for this market', 'status' => 200
                ]);
            }
            return response()->json([
                'data' => $products, 'message' => 'ok', 'status' => 200
            ]);
        } catch (Exception $e) {
            return response()->json([
                'data' => 'an exception occured', 'message' => $e->getMessage(), 'status' => 400
            ]);
        }
    }
    // public function show_product($id)
    // {
    //     try {
    //         $product = Product::with('image')->find($id);
    //         if (!$product) {
    //             return response()->json([
    //   'message' => 'product not found', 'status' => 404
    //    ]);
    //         }
    //         return response()->json([
    //    'data' => $product,
    //     'message' => 'ok',
    //      'status' => 200
    //    ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //       'data' => 'an exception occured',
    //        'message' => $e->getMessage(),
    //         'status' => 400]);
    //     }
    // }
 //returns details of a specific product, including available quantity in markets
    public function show_product_details($id)
    {
        try {
            $amounts = Amount::where('product_id', $id)->with(['product.image'])->get();

            if ($amounts->isEmpty()) {
                return response()->json([
                    'message' => 'Product not found or not available in any market', 'status' => 404
                ]);
            }
            $data = $amounts->map(function ($amount) {
                return [

                        'id' => $amount->product->id,
                        'name' => $amount->product->name,
                        'description' => $amount->product->description,
                        'price' => $amount->product->price,
                        'amount' => $amount->amount,
                        'expiry_date' => $amount->product->expiry_date,
                    'image' => $amount->product->image ? [
                        'id' => $amount->product->image->id,
                        'path' => $amount->product->image->path,
                    ] : null,
                ];

            });
            return response()->json([
                'product' => $data, 'message' => 'ok', 'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 400]);
        }
    }
    public function search_product(Request $request){
        $validated = $request->validate([
            'name' => 'required|string',
        ]);
        $name_product=$request->name;

        $products = Product::where('name', 'like', '%' . $name_product . '%')->get();
        if ($products->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No products found',
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ]);
    }
}
