<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $subject;

    protected string $greeting;

    protected string $message;

    protected string $link;

    protected string $action;

    public function __construct()
    {
        $this->subject = __('[:site] Action required: content at your site received a complaint from a user.', ['site' => getSiteConfig('name')]);
        $this->greeting = __('Hello!');
        $this->message = __('Your action is required.').' '.
            __('User generated content (UGC) at :site just received a new complaint from a user.', ['site' => getSiteConfig('name')]).' '.
            __('Please login to the admin panel or click the link below to manage all user complaints.');
        $this->link = route('admin.complaints');
        $this->action = __('Manage Complaints');
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
            ->subject($this->subject)
            ->greeting($this->greeting)
            ->line($this->message)
            ->action($this->action, $this->link);
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
