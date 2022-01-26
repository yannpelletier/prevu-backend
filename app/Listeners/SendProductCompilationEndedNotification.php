<?php

namespace App\Listeners;

use App\Events\ProductCompilationEnded;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProductCompilationEndedNotification;

class SendProductCompilationEndedNotification
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
     * @param  ProductCompilationEnded  $event
     * @return void
     */
    public function handle(ProductCompilationEnded $event)
    {
        $user = $event->user;
        if(!$user->products()->where('compilation_state', '!=', 'compiled')->exists()){
            $user->notify(new ProductCompilationEndedNotification());
        }
    }
}
