<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyRequest;
use App\Http\Requests\StockRequest;
use App\Http\Requests\StoreRequest;
use App\Http\Requests\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends Controller
{
    protected Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function index()
    {
        return ProductResource::collection($this->product::paginate(10));
    }

    public function store(StoreRequest $request): void
    {
        $this->product::register($request->validated());
    }

    public function show(Product $product): ProductResource
    {
        return ProductResource::make($product);
    }

    public function update(UpdateRequest $request, Product $product): void
    {
        $this->product::change($product->id, $request->validated());
    }

    public function destroy(DestroyRequest $request, Product $product): void
    {
        $this->product::remove($product->name);
    }

    public function calculateTotalInventoryValue(): JsonResponse
    {
        $total = $this->product->calculateTotalInventoryValue();

        return response()->json(['total' => $total]);
    }

    public function stockUpdate(StockRequest $request, Product $product): void
    {
        $product->stockUpdate($request->validated());
    }
}
