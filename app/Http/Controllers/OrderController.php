<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Amount;
use Illuminate\Http\Request;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Kreait\Laravel\Firebase\Facades\Firebase;

class OrderController extends Controller
{
    public function sendNotification($order, $message)
    {
        $notification = [
            'title' => 'Order Update',
            'body' => $message,
        ];

        $firebaseToken = $order->firebase_token;

        if (!$firebaseToken) {
            $firebaseToken = $order->user->firebase_token;
        }

        if ($firebaseToken) {
            $firebase = Firebase::getMessaging();
            $firebase->sendToDevice($firebaseToken, $notification);
        } else {
            logger()->error("No firebase token found for order ID: {$order->id}");
        }
    }


    public function add_to_order(Request $request, $product_id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.01',
            'market_id' => 'required|exists:markets,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'message' => 'Incorrect or missing information',
                'status' => 400,
            ]);
        }

        $stockCheck = Amount::checkStock($product_id, $request->market_id, $request->quantity);
        if (!$stockCheck['status']) {
            return response()->json([
                'status' => 400,
                'message' => $stockCheck['message'],
            ]);
        }

        $product = Product::find($product_id);
        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
                'status' => 404
            ]);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized user',
            ]);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'firebase_token' => $request->firebase_token,
        ]);

        $order->update(['status' => 'in preparation']);

        DB::table('order_product')->insert([
            'order_id' => $order->id,
            'product_id' => $product_id,
            'quantity' => $request->quantity,
            'price' => $product->price,
            'total_cost' => $request->quantity * $product->price,
        ]);


        Amount::where('product_id', $product_id)
            ->where('market_id', $request->market_id)
            ->decrement('amount', $request->quantity);

        $this->sendNotification($order, "Product '{$product->name}' has been added to your order.");

        return response()->json([
            'message' => 'Product added to cart successfully',
            'status' => 200,
            'product' => [
                'name' => $product->name,
                'quantity' => $request->quantity,
                'price' => $product->price,
                'total_cost' => $request->quantity * $product->price,
            ],
            'order_status' => $order->status,
        ]);
    }





    public function cancelOrder($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'status' => 404,
                'message' => 'Order not found',
            ]);
        }

        $createdAt = Carbon::parse($order->created_at);
        if ($createdAt->diffInHours(now()) > 2) {
            $order->update(['status' => 'in delivery']);

            $user = $order->user;



            return response()->json([
                'order_status' => 'in delivery',
                'message' => 'You can no longer cancel your order. It is already being processed (in delivery).',
            ], 400);
        }

        $this->sendNotification($order, "Your order '{$order->id}' status has been updated to 'in delivery'.");

        $orderProducts = DB::table('order_product')->where('order_id', $order->id)->get();
        foreach ($orderProducts as $orderProduct) {
            Amount::where('product_id', $orderProduct->product_id)
                ->where('market_id', $order->market_id)
                ->increment('amount', $orderProduct->quantity);
        }

        $order->update(['status' => 'canceled']);


        $this->sendNotification($order, "Your order '{$order->id}' has been canceled.");

        $user = auth()->user();

        return response()->json([
            'status' => 200,
            'message' => 'Order has been canceled successfully',
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'canceled_at' => $order->updated_at,
            ],
        ]);
    }





    public function modifyOrder(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 404,
                'message' => 'Order not found',
            ]);
        }

        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized access to this order',
            ]);
        }

        $createdAt = Carbon::parse($order->created_at);
        if ($createdAt->diffInHours(now()) > 2) {
            $order->update(['status' => 'in delivery']);
            $user = $order->user;

            $this->sendNotification($order, "Your order '{$order->id}' status has been updated to 'in delivery'.");


            return response()->json([
                'order_status' => 'in delivery',
                'message' => 'Order cannot be modified as it is already in delivery.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'address' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|exists:products,name',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid data',
                'errors' => $validator->errors(),
            ]);
        }

        if ($request->filled('address')) {
            DB::table('addresses')->updateOrInsert(
                ['user_id' => $order->user_id],
                ['location' => $request->address]
            );
        }

        foreach ($request->products as $productData) {
            $product = Product::where('name', $productData['name'])->first();
            $existingOrderProduct = DB::table('order_product')
                ->where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->first();

            $quantityDifference = $productData['quantity'] - ($existingOrderProduct->quantity ?? 0);

            if ($quantityDifference > 0) {
                Amount::where('product_id', $product->id)
                    ->where('market_id', $order->market_id)
                    ->decrement('amount', $quantityDifference);
            } else {
                Amount::where('product_id', $product->id)
                    ->where('market_id', $order->market_id)
                    ->increment('amount', abs($quantityDifference));
            }

            DB::table('order_product')->updateOrInsert(
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
                    'total_cost' => $product->price * $productData['quantity'],
                ]
            );
        }

        $order->update(['status' => 'Modified']);

        $this->sendNotification($order, "Your order '{$order->id}' has been modified.");

        $user = User::find($order->user_id);



        $orderDetails = $order->load(['products' => function ($query) {
            $query->select('products.id', 'products.name', 'order_product.quantity', 'order_product.total_cost');
        }]);

        return response()->json([
            'status' => 200,
            'message' => 'Order modified successfully',
            'order' => $orderDetails->products,
        ]);
    }
}
