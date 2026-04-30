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

    it('should index products successfully', function () {
        ProductFactory::new()->create(['name' => 'Test Product 1']);
        ProductFactory::new()->create(['name' => 'Test Product 2']);
        ProductFactory::new()->create(['name' => 'Test Product 3']);

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->get('/api/v1/products');

        $response->assertOk();
        expect($response->json())->toHaveKey('data');
        expect(count($response->json('data')))->toBe(3);
    });

    it('should show product successfully', function () {
        ProductFactory::new()->create(['name' => 'Test Product 1']);

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->get('/api/v1/products/1');

        $response->assertOk();
        expect($response->json())->toHaveKey('data');
        expect(count($response->json('data')))->toBe(5);
    });

    it('should fail to register product without name', function () {
        $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => '',
                'description' => 'This is a test product.',
                'amount' => 20,
                'quantity' => 10,
            ])->assertStatus(422)
            ->assertJson(['message' => 'The name field is required.']);
    });

    it('should fail to register product with shorter than 3 characters name', function () {
        $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => 'No',
                'description' => 'This is a test product.',
                'amount' => 20,
                'quantity' => 10,
            ])->assertStatus(422)
            ->assertJson(['message' => 'The name field must be at least 3 characters.']);
    });

    it('should fail to register product with longer than 50 characters name', function () {
        $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => str_repeat('T', 51),
                'description' => 'This is a test product.',
                'amount' => 20,
                'quantity' => 10,
            ])->assertStatus(422)
            ->assertJson(['message' => 'The name field must not be greater than 50 characters.']);
    });

    it('should fail to register product with description longer than 255', function () {
        $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => 'Test Product 1',
                'description' => str_repeat('T', 256),
                'amount' => 20,
                'quantity' => 10,
            ])->assertStatus(422)
            ->assertJson(['message' => 'The description field must not be greater than 255 characters.']);
    });

    it('should fail to register product without amount', function () {
        $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => 'Test Product 1',
                'description' => 'This is a test product.',
                'amount' => '',
                'quantity' => 10,
            ])->assertStatus(422)
            ->assertJson(['message' => 'The amount field is required.']);
    });

    it('should fail to register product with zero amount', function () {
        $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => 'Test Product 1',
                'description' => 'This is a test product.',
                'amount' => 0,
                'quantity' => 10,
            ])->assertStatus(422)
            ->assertJson(['message' => 'The amount field must be at least 0.01.']);
    });

    it('should fail to register product with negative amount', function () {
        $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => 'Test Product 1',
                'description' => 'This is a test product.',
                'amount' => -1,
                'quantity' => 10,
            ])->assertStatus(422)
            ->assertJson(['message' => 'The amount field must be at least 0.01.']);
    });

    it('should fail to register product with negative quantity', function () {
        $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => 'Test Product 1',
                'description' => 'This is a test product.',
                'amount' => 10,
                'quantity' => -1,
            ])->assertStatus(422)
            ->assertJson(['message' => 'The quantity field must be at least 0.']);
    });

    it('should fail to register product without quantity', function () {
        $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => 'Test Product 1',
                'description' => 'This is a test product.',
                'amount' => 10,
                'quantity' => '',
            ])->assertStatus(422)
            ->assertJson(['message' => 'The quantity field is required.']);
    });

    it('should store product successfully', function () {
        $response = $this->withHeaders(['accept' => 'application/json'])
            ->post('/api/v1/products', [
                'name' => 'Test Product 1',
                'description' => 'This is a test product.',
                'amount' => 20,
                'quantity' => 10,
            ]);

        $response->assertOk();
    });

    it('should fail to update product if name does not match', function () {
        $product = ProductFactory::new()->create(['name' => 'Test Product 1']);

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/products/{$product->id}", [
                'name' => 'Test Product',
                'description' => 'This is a test product.',
                'amount' => 20,
                'quantity' => 10,
            ]);

        $response->assertStatus(422)->assertJson(['message' => 'The selected name is invalid.']);
    });

    it('should fail to update product if description longer than 255 characters', function () {
        $product = ProductFactory::new()->create(['name' => 'Test Product 1']);

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/products/{$product->id}", [
                'name' => 'Test Product 1',
                'description' => str_repeat('T', 256),
                'amount' => 20,
                'quantity' => 10,
            ]);

        $response->assertStatus(422)->assertJson(['message' => 'The description field must not be greater than 255 characters.']);
    });

    it('should fail to update product if amount is negative', function () {
        $product = ProductFactory::new()->create(['name' => 'Test Product 1']);

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/products/{$product->id}", [
                'name' => 'Test Product 1',
                'description' => 'This is a test product.',
                'amount' => -20,
                'quantity' => 10,
            ]);

        $response->assertStatus(422)->assertJson(['message' => 'The amount field must be at least 0.01.']);
    });

    it('should fail to update product if amount is equals 0', function () {
        $product = ProductFactory::new()->create(['name' => 'Test Product 1']);

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/products/{$product->id}", [
                'name' => 'Test Product 1',
                'description' => 'This is a test product.',
                'amount' => 0,
                'quantity' => 10,
            ]);

        $response->assertStatus(422)->assertJson(['message' => 'The amount field must be at least 0.01.']);
    });

    it('should fail to update product if quantity is negative', function () {
        $product = ProductFactory::new()->create(['name' => 'Test Product 1']);

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/products/{$product->id}", [
                'name' => 'Test Product 1',
                'description' => 'This is a test product.',
                'amount' => 10,
                'quantity' => -10,
            ]);

        $response->assertStatus(422)->assertJson(['message' => 'The quantity field must be at least 0.']);
    });

    it('should update product successfully even without name field', function () {
        $product = ProductFactory::new()->create([
            'name' => 'Test Product 1',
            'description' => 'This is a test product.',

        ]);

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/products/{$product->id}", [
                'description' => 'This is the best product.',
                'amount' => 1000000,
                'quantity' => 10,

            ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product 1',
            'description' => 'This is the best product.',
            'amount' => 100000000,
            'quantity' => 10,
        ]);
    });
});
