<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\UserCreated' => [
            'App\Listeners\UserCreatedListener'
        ],
        'App\Events\ProductPurchased' => [
            'App\Listeners\SendSaleNotification',
            'App\Listeners\SendPurchaseNotification'
        ],
        'App\Events\ModelCompilationStateChanged' => [
            'App\Listeners\SetModelCompilationState'
        ],
        'App\Events\ProductCompilationEnded' => [
            'App\Listeners\SendProductCompilationEndedNotification',
            'App\Listeners\CreateProductPublicThumbnail',
        ],
        'App\Events\FeedbackSent' => [
            'App\Listeners\FeedbackSentListener'
        ],
        'App\Events\ContactSent' => [
            'App\Listeners\ContactSentListener'
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
