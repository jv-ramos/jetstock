<?php

namespace App\Services;

use App\Models\Product;

class ProductServices
{
    protected Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    // public static function calculateStockTotal(array $array, float $total)
    // {
    //     for ($i = 0; $i < count($array); $i++) {
    //         $total += $array[$i]['amount'] * $array[$i]['quantity'];
    //
    //         return $total;
    //     }
    // }

    public static function existsAndHasMoreThan(Product $product, int $quantity)
    {
        if ($product->exists && $product->exists < $quantity) {
            throw new \InvalidArgumentException(
                __('message.products.quantity_exceed', ['name' => $product->name, 'quantity' => $product->quantity])
            );
        };
    }
}
