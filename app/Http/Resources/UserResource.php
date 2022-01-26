<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'country' => $this->country,
            'stripe_connect_id' => $this->stripe_connect_id,
            'stripe_customer_id' => $this->stripe_customer_id,
            'send_sale_notifications' => $this->send_sale_notifications,
            'send_ip_notifications' => $this->send_ip_notifications,
            'analytics_currency' => $this->analytics_currency,
            'sale_currency' => $this->sale_currency,
            'confirmed' => $this->confirmed,
            'first_time_login' => $this->first_time_login
        ];
    }
}
