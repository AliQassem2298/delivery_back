<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amount extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'market_id',
        'amount',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function market()
    {
        return $this->belongsTo(Market::class);
    }
    public static function checkStock($productId, $marketId, $requiredQuantity)
    {
        $amount = self::where('product_id', $productId)
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
