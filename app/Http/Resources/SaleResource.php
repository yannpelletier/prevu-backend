<?php

namespace App\Http\Resources;

use App\Purchase;
use Illuminate\Http\Resources\Json\Resource;

class SaleResource extends Resource
{
    public $collects = Purchase::class;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $productUrl = config('app.api_url') . "/purchase/" . $this->id . '/';

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'currency' => $this->currency,
            'original' => $productUrl . 'original',
            'thumbnail' => $productUrl . "thumbnail",
            'buyer_email' => $this->buyer->email,
            'date' => $this->created_at->diffForHumans(),
        ];
    }
}
