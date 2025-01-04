<?php

namespace Database\Seeders;

use App\Models\Amount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amounts = [
            [
                'product_id' => 1, // Apple
                'market_id' => 1, // Central Market
                'amount' => 200.5, // Amount of apples in Central Market
            
            ],
            [
                'product_id' => 2, // Laptop
                'market_id' => 2, // Green Valley Market
                'amount' => 50.0, // Amount of laptops in Green Valley Market
            
            ],
            [
                'product_id' => 3, // Shampoo
                'market_id' => 3, // Tech Plaza
                'amount' => 100.0, // Amount of shampoo in Tech Plaza
          
            ],
        ];

        Amount::insert($amounts);
    }
}
