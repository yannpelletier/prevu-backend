<?php

namespace App\Http\Controllers;

use App\Asset;
use App\Http\Requests\AssetRequest\AssetDestroyRequest;
use App\Http\Requests\AssetRequest\AssetStoreRequest;
use App\Http\Resources\AssetResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $assets = $user->assets;
        return AssetResource::collection($assets);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AssetStoreRequest $request
     * @return AssetResource
     */
    public function store(AssetStoreRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $newAsset = $user->assets()->create($validated);
        return AssetResource::make($newAsset);
    }

    /**
     * Remove the asset from storage.
     *
     * @param AssetStoreRequest $request
     * @param Asset $asset
     * @return void
     * @throws \Exception
     */
    public function destroy(AssetDestroyRequest $request, Asset $asset)
    {
        $asset->delete();
    }
}
