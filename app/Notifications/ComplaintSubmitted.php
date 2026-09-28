<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

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
        $site = getSiteConfig('name');

        return (new MailMessage)
            ->subject(__('[:site] Action required: content at your site received a complaint from a user.', ['site' => $site]))
            ->greeting(__('Hello!'))
            ->line(__('Your action is required.').' '
                .__('User generated content (UGC) at :site just received a new complaint from a user.', ['site' => $site]).' '
                .__('Please login to the admin panel or click the link below to manage all user complaints.'))
            ->action(__('Manage Complaints'), route('admin.complaints'));
    }
}
