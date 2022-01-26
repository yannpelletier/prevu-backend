<?php

namespace App\Listeners;

use App\Events\ContactSent;
use App\Notifications\ContactReceivedNotification;
use App\Notifications\FeedbackReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class ContactSentListener
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
    public function handle(ContactSent $event)
    {
        foreach (config('constants.admins') as $adminEmail) {
            Notification::route('mail', $adminEmail)->notify(new ContactReceivedNotification($event->contact));
        }
    }
}
