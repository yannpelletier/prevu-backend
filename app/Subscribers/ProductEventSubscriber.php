<?php


namespace App\Subscribers;


class ProductEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleUserLogin($event) {}

    /**
     * Handle user logout events.
     */
    public function handleUserLogout($event) {}

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\Product\CompilationStarted',
            'App\Listeners\UserEventSubscriber@handleUserLogin'
        );

        $events->listen(
            'App\Events\Product\CompilationEnded',
            'App\Listeners\UserEventSubscriber@handleUserLogout'
        );
    }
}
