<?php

namespace App\Http\Controllers\V1;

use App\Models\Product;
use App\Http\Requests\StoreRequest;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function update(Request $request, Product $product): void
    {
        //
    }

    public function destroy(Product $product): void
    {
        //
    }
}
