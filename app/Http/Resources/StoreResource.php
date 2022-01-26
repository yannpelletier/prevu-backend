<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $storeUrl = config('app.frontend_url') . "/store/" . $this->slug . '/';

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'currency' => $this->currency,
            'root_sections' => $this->root_sections,
            'root_sections_info' => $this->rootSectionsInfo,
            //'custom_sections' => $this->customSections,
            'confirmed' => $this->confirmed,

            //links
            'link' => $storeUrl,
        ];
    }
}
