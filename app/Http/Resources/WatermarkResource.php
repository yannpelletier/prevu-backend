<?php

namespace App\Http\Resources;

use App\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class WatermarkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'dimension' => $this->dimension,
            'user_id' => $this->user_id,

            //Links
            'link' => $this->getFileUrl(),
        ];
    }
}
