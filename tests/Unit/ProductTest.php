<?php

namespace Tests\Unit;

use App\Exceptions\Product\InvalidProductAttributeException;
use App\Models\Product;
use Database\Factories\ProductFactory;

describe('Product', function () {
    /**
     * PRODUCT CREATION
     */
    it('throws exception if product name is shorter than 3 characters', function () {
        Product::register([
            'name' => 'ab',
            'description' => 'Test product',
            'amount' => 1000,
            'quantity' => 10,
        ]);
    })->throws(
        InvalidProductAttributeException::class,
        'Name must be at least 3 characters long.'
    );

    it('throws exception if product name exceed 50 characters', function () {
        Product::register([
            'name' => str_repeat('a', 51),
            'description' => 'Test product',
            'amount' => 1000,
            'quantity' => 10,
        ]);
    })->throws(
        InvalidProductAttributeException::class,
        'Name must not exceed 50 characters'
    );

    it('throws exception if description is longer than 255 characters', function () {
        Product::register([
            'name' => 'Test product',
            'description' => str_repeat('a', 256),
            'amount' => 1000,
            'quantity' => 10,
        ]);
    })->throws(
        InvalidProductAttributeException::class,
        'Description must not exceed 255 characters'
    );

    /**
     * STOCK SETUP
     */
    it('throws exception when amount is null', function () {
        ProductFactory::new()->make(['amount' => null]);
    })->throws(
        InvalidProductAttributeException::class,
        'Forbidden operation'
    );

    it('throws exception when quantity is null', function () {
        ProductFactory::new()->make(['quantity' => null]);
    })->throws(
        InvalidProductAttributeException::class,
        'Quantity must be equals or greater than 0 (ZERO).'
    );

    it('throws exception when amount is negative', function () {
        ProductFactory::new()->make(['amount' => -10]);
    })->throws(
        InvalidProductAttributeException::class,
        'Forbidden operation'
    );

    it('throws exception when quantity is negative', function () {
        ProductFactory::new()->make(['quantity' => -5]);
    })->throws(
        InvalidProductAttributeException::class,
        'Quantity must be equals or greater than 0 (ZERO).'
    );

    /**
     * STOCK DECREMENT
     */
    it('rejects to subtract quantity by negative values', function () {
        $product = ProductFactory::new()->make();
        $product->stockDecrement(-10);
    })->throws(
        InvalidProductAttributeException::class,
        'Forbidden operation'
    );

    it('rejects to subtract quantity by 0', function () {
        $product = ProductFactory::new()->make();
        $product->stockDecrement(0);
    })->throws(
        InvalidProductAttributeException::class,
        'Forbidden operation'
    );

    it('fails to decrease stock bellow 0', function () {
        $product = ProductFactory::new()->make([
            'name' => 'Test Product',
            'quantity' => 0,
        ]);
        $product->stockDecrement(2);
    })->throws(
        InvalidProductAttributeException::class,
        'Forbidden operation'
    );

    /**
     * STOCK INCREMENT
     */
    it('rejects to increment quantity by negative values', function () {
        $product = ProductFactory::new()->make();
        $product->stockIncrement(-10);
    })->throws(
        InvalidProductAttributeException::class,
        'Forbidden operation'
    );

    it('rejects to increment quantity by 0', function () {
        $product = ProductFactory::new()->make();
        $product->stockDecrement(0);
    })->throws(
        InvalidProductAttributeException::class,
        'Forbidden operation'
    );
});
