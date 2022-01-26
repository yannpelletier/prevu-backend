<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeedbackSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user, $message;

    /**
     * Create a new event instance.
     *
     * @param $user
     * @param $message
     */
    public function __construct($user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }
}
