<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;

describe('Product', function () {
    uses(RefreshDatabase::class);

    it('throws exception if product name is not unique', function () {
        ProductFactory::new()->create(['name' => 'Test Product']);
        ProductFactory::new()->create(['name' => 'Test Product']);
    })->throws(
        InvalidArgumentException::class,
        'Product with name Test Product already exists'
    );

    it('throws exception if product amount is not greater than 0', function () {
        Product::register([
            'name' => 'Test Product',
        ]);
    })->throws(
        InvalidArgumentException::class,
        'Forbidden operation'
    );

    it('should create product successfully', function () {
        $product = Product::register([
            'name' => 'Test Product',
            'description' => 'This is a test product.',
            'amount' => 1000,
            'quantity' => 10,
        ]);

        expect($product->exists)->toBeTrue();
        expect($product->name)->toBe('Test Product');
        expect($product->description)->toBe('This is a test product.');
        expect($product->amount)->toBe(1000.00);
        expect($product->getAmountInCentsAttribute())->toBe(100000); # testing accessor method
        expect($product->quantity)->toBe(10);
    });

    /**
     * CALCULATE
     */
    it('should calculate total inventory successfully', function () {
        Product::register([
            'name' => 'Test Product 1',
            'description' => 'This is a test product.',
            'amount' => 20,
            'quantity' => 10,
        ]);
        Product::register([
            'name' => 'Test Product 2',
            'description' => 'This is a test product.',
            'amount' => 10,
            'quantity' => 5,
        ]);
        $product = new Product();

        expect($product->calculateTotalInventoryValue())->toBe(250.0);
    });
});
