<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Warns the current address of an account that someone asked to move it to
 * another address, so a hijacked session can't quietly take the account.
 */
class EmailChangeRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $newEmail)
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
            ->subject(__('[:site] Your email address is being changed', ['site' => getSiteConfig('name')]))
            ->greeting(__('Hello!'))
            ->line(__('Someone asked to change the email address of your :site account to :email. The change only takes effect once the code we sent to that address is entered.', ['site' => getSiteConfig('name'), 'email' => $this->newEmail]))
            ->line(__('If you did not ask for this, reset your password right away to sign out everyone else.'))
            ->action(__('Reset password'), route('login'));
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
