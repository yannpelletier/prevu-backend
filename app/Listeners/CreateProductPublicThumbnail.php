<?php

namespace App\Listeners;

use App\Events\ProductCompilationEnded;

class CreateProductPublicThumbnail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ProductCompilationEnded $event)
    {
        $product = $event->product;
        $product->createPublicThumbnail();
    }
}
