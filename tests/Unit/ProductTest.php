<?php

use App\Models\Product;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Product', function () {
    uses(RefreshDatabase::class);

    /*
    * PRODUCT CREATION
    */
    it('throws exception if product name is shorter than 3 characters', function () {
        Product::register([
            'name' => 'ab',
            'description' => 'Test product',
            'amount' => 1000,
            'quantity' => 10,
        ]);
    })->throws(InvalidArgumentException::class,
        'Name must be at least 3 characters long.');

    it('throws exception if product name exceed 50 characters', function () {
        Product::register([
            'name' => str_repeat('a', 51),
            'description' => 'Test product',
            'amount' => 1000,
            'quantity' => 10,
        ]);
    })->throws(InvalidArgumentException::class,
        'Name must not exceed 50 characters');

    it('throws exception if description is longer than 255 characters', function () {
        Product::register([
            'name' => 'Test product',
            'description' => str_repeat('a', 256),
            'amount' => 1000,
            'quantity' => 10,
        ]);
    })->throws(InvalidArgumentException::class,
        'Description must not exceed 255 characters');

    /*
    * STOCK SETUP
    */
    it('throws exception when amount is null', function () {
        ProductFactory::new()->make(['amount' => null]);
    })->throws(InvalidArgumentException::class,
        'Amount must be greater than 0 (ZERO).');

    it('throws exception when quantity is null', function () {
        ProductFactory::new()->make(['quantity' => null]);
    })->throws(InvalidArgumentException::class,
        'Quantity must be equals or greater than 0 (ZERO).');

    it('throws exception when amount is negative', function () {
        ProductFactory::new()->make(['amount' => -10]);
    })->throws(InvalidArgumentException::class,
        'Amount must be greater than 0 (ZERO).');

    it('throws exception when quantity is negative', function () {
        ProductFactory::new()->make(['quantity' => -5]);
    })->throws(InvalidArgumentException::class,
        'Quantity must be equals or greater than 0 (ZERO).');

    /*
    * STOCK DECREMENT
    */
    it('rejects to subtract quantity by negative values', function () {
        $product = ProductFactory::new()->make();
        $product->stockDecrement(-10);
    })->throws(InvalidArgumentException::class,
        'Forbidden operation');

    it('rejects to subtract quantity by 0', function () {
        $product = ProductFactory::new()->make();
        $product->stockDecrement(0);
    })->throws(InvalidArgumentException::class,
        'Forbidden operation');

    it('fails to decrease stock bellow 0', function () {
        $product = ProductFactory::new()->make([
            'name' => 'Test Product', 'quantity' => 0,
        ]);
        $product->stockDecrement(2);
    })->throws(RuntimeException::class,
        'Not enough Test Product in stock');

    /*
    * STOCK INCREMENT
    */
    it('rejects to increment quantity by negative values', function () {
        $product = ProductFactory::new()->make();
        $product->stockIncrement(-10);
    })->throws(InvalidArgumentException::class,
        'Forbidden operation');

    it('rejects to increment quantity by 0', function () {
        $product = ProductFactory::new()->make();
        $product->stockDecrement(0);
    })->throws(InvalidArgumentException::class,
        'Forbidden operation');
});
