<?php

namespace App\Listeners;

use App\Events\ProductPurchased;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PurchaseConfirmedNotification;

class SendPurchaseNotification
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
     * @param  ProductPurchased  $event
     * @return void
     */
    public function handle(ProductPurchased $event)
    {
        Notification::send([$event->buyer], new PurchaseConfirmedNotification());
    }
}
