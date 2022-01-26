<?php

namespace App\Listeners;

use App\Events\FeedbackSent;
use App\Notifications\FeedbackReceivedNotification;
use Illuminate\Support\Facades\Notification;

class FeedbackSentListener
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
     * @param object $event
     * @return void
     */
    public function handle(FeedbackSent $event)
    {
        foreach (config('constants.admins') as $adminEmail) {
            Notification::route('mail', $adminEmail)->notify(new FeedbackReceivedNotification($event->user, $event->message));
        }
    }
}
