<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->notifiable_id	,
            'description' => $this->data['description'],
            'date' => $this->created_at->diffForHumans(),
            'read' => $this->read_at->diffInSeconds(now()) > 5,
        ];
    }
}
