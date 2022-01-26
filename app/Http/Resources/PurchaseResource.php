<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PurchaseResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // TODO: product URL
        $productUrl = config('app.api_url') . "/purchases/" . $this->id . '/';

        return [
            'id' => $this->id,
            'buyer_id' => $this->buyer_id,
            'file_id' => $this->file_id,
            'extension' => $this->extension,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'original' => $productUrl . 'original',
            'thumbnail' => $productUrl . "thumbnail",
            'download' => $productUrl . "download",
            'date' => $this->created_at->diffForHumans(),
        ];
    }
}
