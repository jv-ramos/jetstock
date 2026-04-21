<?php

use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Product', function () {
    uses(RefreshDatabase::class);

    /*
    * STOCK SETTUP
    */
    it('null amount should be set to 0', function () {
        $product = ProductFactory::new()->make(['amount' => null]);
        expect($product->amount)->toBe(0);
    });

    it('null quantity should be set to 0', function () {
        $product = ProductFactory::new()->make(['quantity' => null]);
        expect($product->quantity)->toBe(0);
    });

    it('throws exception when amount is negative', function () {
        ProductFactory::new()->make(['amount' => -10]);
    })->throws(InvalidArgumentException::class, 'Amount must be greater than 0 (ZERO).');

    it('throws exception when quantity is negative', function () {
        ProductFactory::new()->make(['quantity' => -5]);
    })->throws(InvalidArgumentException::class, 'Quantity must be greater than 0 (ZERO).');

    /*
    * STOCK DECREMENT
    */
    it('rejects to subtract quantity by negative values', function () {
        $product = ProductFactory::new()->make();
        $product->stockDecrement(-10);
    })->throws(InvalidArgumentException::class, 'Forbidden operation');

    it('rejects to subtract quantity by 0', function () {
        $product = ProductFactory::new()->make();
        $product->stockDecrement(0);
    })->throws(InvalidArgumentException::class, 'Forbidden operation');

    it('fails to decrease stock bellow 0', function () {
        $product = ProductFactory::new()->make(['quantity' => 0]);
        $result = $product->stockDecrement(2);
        expect($result)->toBe("Not enough {$product->name} in stock");
        expect($product->quantity)->toBe(0);
    });

    /*
    * STOCK INCREMENT
    */
    it('rejects to increment quantity by negative values', function () {
        $product = ProductFactory::new()->make();
        $product->stockIncrement(-10);
    })->throws(InvalidArgumentException::class, 'Forbidden operation');


    it('rejects to increment quantity by 0', function () {
        $product = ProductFactory::new()->make();
        $product->stockDecrement(0);
    })->throws(InvalidArgumentException::class, 'Forbidden operation');
});
