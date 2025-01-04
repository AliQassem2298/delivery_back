<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products_Data = [
            [
                'name' => 'Apple',
                'description' => 'Fresh red apples from the farm.',
                'price' => 3.50,
                'expiry_date' => now()->addDays(30),
            ],
            [
                'name' => 'Laptop',
                'description' => 'High-performance laptop for work and gaming.',
                'price' => 1500.00,
                'expiry_date' => now()->addYears(2),
            ],
            [
                'name' => 'Shampoo',
                'description' => 'Herbal shampoo for all hair types.',
                'price' => 10.00,
                'expiry_date' => now()->addMonths(6),
            ],
        ];

        $imagePaths = [
            'images/1732900344.magic-cube-cube-puzzle-play.jpg',
            'images/1732900415.sunflower-1127174_1280.jpg',
            'images/1732900450.sunflowers-3292932_1280.jpg',
        ];

        foreach ($products_Data as $index => $product_Data) {
  
            $product = Product::create($product_Data);

        
            $image = new Image();
            $image->path = $imagePaths[$index];
            $image->imageable_type = Product::class;
            $image->imageable_id = $product->id;
            $image->save();

            $product->image()->save($image);
        }
    }
}
