<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use \Illuminate\Database\Eloquent\Collection;

class ProductPurchased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $buyer, $seller, $products;

    public function __construct($seller, $buyer, Collection $products)
    {
        $this->seller = $seller;
        $this->buyer = $buyer;
        $this->products = $products;
    }
}
