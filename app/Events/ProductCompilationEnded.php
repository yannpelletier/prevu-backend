<?php

namespace App\Events;

use App\User;
use App\Product;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;


class ProductCompilationEnded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $product;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Product $product
     */
    public function __construct(User $user, Product $product)
    {
        $this->user = $user;
        $this->product = $product;
    }
}
