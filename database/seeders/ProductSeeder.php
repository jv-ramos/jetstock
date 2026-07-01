<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Database\Seeders\services\FetchApi;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = FetchApi::getProducts();

        foreach ($products as $product) {
            Product::create([
                'name' => substr($product['title'], 0, 50),
                'image' => $product['image'],
                'description' => substr($product['description'], 0, 255),
                'amount' => $product['price'],
                'quantity' => fake()->randomNumber(3),
            ]);
        };
    }
}
