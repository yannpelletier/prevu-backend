<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest\ProductDestroyRequest;
use App\Http\Requests\ProductRequest\ProductShowOriginalRequest;
use App\Http\Requests\ProductRequest\ProductStoreRequest;
use App\Http\Requests\ProductRequest\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Jobs\Compilation\CompileImagePreview;
use App\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProductController extends Controller
{
    /**
     * Lists all the current user products
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $user = Auth::user();
        $products = $user->products;
        ProductResource::requestOriginalLink(true);
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return ProductResource
     */
    public function store(ProductStoreRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $newProduct = $user->products()->create($validated);

        // Appends a random string to the slug if already used by the same user
        $productWithSameSlug = Product::where('slug', $newProduct->slug)
            ->where('user_id', $user->id)
            ->where('id', '!=', $newProduct->id)->count();
        if ($productWithSameSlug > 0) {
            $newProduct->slug = $newProduct->slug . '-' . Str::random(5);
            $newProduct->save();
        }

        ProductResource::requestOriginalLink(true);
        return ProductResource::make($newProduct, true);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return ProductResource
     */
    public function show(Product $product)
    {
        ProductResource::requestOriginalLink(true);
        return ProductResource::make($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProductUpdateRequest $request
     * @param Product $product
     * @return ProductResource
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $validated = $request->validated();

        $product->fill($validated);
        $product->save();

        ProductResource::requestOriginalLink(true);
        return ProductResource::make($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ProductDestroyRequest $request
     * @param Product $product
     * @return void
     * @throws \Exception
     */
    public function destroy(ProductDestroyRequest $request, Product $product)
    {
        $product->delete();
    }
}
