<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest\StoreStoreRequest;
use App\Http\Requests\StoreRequest\StoreUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Store;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function store(StoreStoreRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();
        if ($user->store)
            abort(422, trans('exceptions.stores.cannot_create_more_stores'));

        $store = $user->store()->create(array_merge($validated, ['attributes' => []]));
        return StoreResource::make($store);
    }

    /**
     * Fetches a store by its slug or id.
     *
     * @return StoreResource
     */
    public function show($id)
    {
        $store = Store::where('slug', $id)->orWhere('id', 'like', $id)->firstOrFail();
        return StoreResource::make($store);
    }

    public function getProduct($id, $productSlug)
    {
        $store = Store::where('slug', $id)->orWhere('id', 'like', $id)->firstOrFail();
        $storeOwner = $store->user;
        $product = $storeOwner->products()->where('slug', $productSlug)->firstOrFail();
        return ProductResource::make($product);
    }

    public function getProducts($id)
    {
        $store = Store::where('slug', $id)->orWhere('id', 'like', $id)->firstOrFail();
        $storeOwner = $store->user;
        return ProductResource::collection($storeOwner->products);
    }

    public function update(StoreUpdateRequest $request, Store $store)
    {
        $validated = $request->validated();
        $store->fill($validated);

        /*
         * root_sections is not fillable.
         * Only the fields or root_sections provided in the request get modified.
         */
        if ($request->input('root_sections')) {
            $newRootSections = array_merge([], $store->root_sections);
            foreach ($request->input('root_sections') as $sectionId => $sectionData) {
                $newRootSections[$sectionId]['variant'] = $sectionData['variant'];
                foreach ($sectionData['parameters'] as $parameterId => $parameterData) {
                    $newRootSections[$sectionId]['parameters'][$parameterId] = $parameterData;
                }
            }
            $store->root_sections = $newRootSections;
        }
        $store->save();
        return StoreResource::make($store);
    }
}
