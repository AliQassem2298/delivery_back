<?php

namespace Database\Seeders;

use App\Models\Image;

use App\Models\Market;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Markets_Data = [
            [
                'name' => 'Central Market',
                'description' => 'A large market offering a variety of goods.',
                'address' => '123 Main Street, Cityville',

            ],
            [
                'name' => 'Green Valley Market',
                'description' => 'Organic produce and natural products.',
                'address' => '456 Green Road, Townsville',

            ],
            [
                'name' => 'Tech Plaza',
                'description' => 'Specialized in electronics and gadgets.',
                'address' => '789 Tech Avenue, Metropolis',

            ],
        ];
        $imagePaths = [
            'images/1732900344.magic-cube-cube-puzzle-play.jpg',
            'images/1732900415.sunflower-1127174_1280.jpg',
            'images/1732900450.sunflowers-3292932_1280.jpg',
        ];

        foreach ($Markets_Data as $index => $Market_Data) {

            $market = Market::create($Market_Data);


            $image = new Image();
            $image->path = $imagePaths[$index];
            $image->imageable_type = Market::class;
            $image->imageable_id = $market->id;
            $image->save();

            $market->image()->save($image);
        }
    }
}
