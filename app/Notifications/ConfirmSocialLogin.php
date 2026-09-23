<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmSocialLogin extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $service, public string $confirmUrl)
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
            ->subject(__('[:site] Confirm your :service sign-in', ['site' => getSiteConfig('name'), 'service' => ucfirst($this->service)]))
            ->greeting(__('Hello!'))
            ->line(__('Someone just tried to sign in to your :site account using :service.', ['site' => getSiteConfig('name'), 'service' => ucfirst($this->service)]))
            ->line(__('If this was you, click below to confirm and finish signing in. This link expires in 30 minutes.'))
            ->action(__('Confirm sign-in'), $this->confirmUrl)
            ->line(__('If you did not try to sign in, you can safely ignore this email.'));
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
