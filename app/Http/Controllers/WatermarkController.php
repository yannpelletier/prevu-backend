<?php

namespace App\Http\Controllers;

use App\Http\Requests\WatermarkRequest\WatermarkUpdateRequest;
use App\Watermark;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\WatermarkResource;
use App\Http\Requests\WatermarkRequest\WatermarkStoreRequest;
use App\Http\Requests\WatermarkRequest\WatermarkDestroyRequest;

class WatermarkController extends Controller
{
    /**
     * List the user's custom and default watermarks
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index() {
        $user = Auth::user();
        $watermarks = $user->watermarks;
        return WatermarkResource::collection($watermarks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param WatermarkStoreRequest $request
     * @return WatermarkResource
     */
    public function store(WatermarkStoreRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $newWatermark = $user->watermarks()->create($validated);
        return WatermarkResource::make($newWatermark);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param WatermarkUpdateRequest $request
     * @param Watermark $watermark
     * @return WatermarkResource
     */
    public function update(WatermarkUpdateRequest $request, Watermark $watermark)
    {
        $validated = $request->validated();

        $watermark->fill($validated);
        $watermark->save();
        return WatermarkResource::make($watermark);
    }

    /**
     * Remove the asset from storage.
     *
     * @param WatermarkDestroyRequest $request
     * @param Watermark $watermark
     * @return void
     * @throws \Exception
     */
    public function destroy(WatermarkDestroyRequest $request, Watermark $watermark)
    {
        $watermark->delete();
    }
}
