<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailCode extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $code, public int $expiresInMinutes)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('[:site] Your verification code', ['site' => getSiteConfig('name')]))
            ->greeting(__('Hello!'))
            ->line(__('Use the following code to verify your email address at :site. It expires in :minutes minutes.', ['site' => getSiteConfig('name'), 'minutes' => $this->expiresInMinutes]))
            ->line('**'.$this->code.'**')
            ->line(__('If you did not create an account, you can safely ignore this email.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
