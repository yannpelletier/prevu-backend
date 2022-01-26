<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\User;

class SaleConfirmedNotification extends Notification
{
    use Queueable;

    private $buyer;

    public function __construct(User $buyer)
    {
        $this->buyer = $buyer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('PrevU sale')
            ->greeting("New purchase from a customer!")
            ->line("Hi we're sending you this email to notify you that you've just sold one or more files to a customer.")
            ->action('Go to dashboard', getUrl('/dashboard'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'description' => 'You have sold one or more files to a customer.'
        ];
    }
}
