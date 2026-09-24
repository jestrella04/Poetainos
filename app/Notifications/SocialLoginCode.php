<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SocialLoginCode extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $code, public string $service, public int $expiresInMinutes)
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
        $service = ucfirst($this->service);

        return (new MailMessage)
            ->subject(__('[:site] Your :service sign-in code', ['site' => getSiteConfig('name'), 'service' => $service]))
            ->greeting(__('Hello!'))
            ->line(__('You are signing in to your :site account with :service for the first time. Use the following code to confirm it is you. It expires in :minutes minutes.', ['site' => getSiteConfig('name'), 'service' => $service, 'minutes' => $this->expiresInMinutes]))
            ->line('**'.$this->code.'**')
            ->line(__('You only need to do this once: from now on you will sign in with :service directly.', ['service' => $service]))
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
