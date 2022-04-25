<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subject;
    protected $greeting;
    protected $message;
    protected $link;
    protected $action;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->subject = getSiteConfig('name') . __('Action required: content at your site received a complaint from a user.');
        $this->greeting = __('Hello!');
        $this->message = __('Your action is required.') .
            __('User generated content (UGC) at :site just received a new complaint from a user.', ['site' => getSiteConfig('name')]) .
            __('Please login to the admin panel or click the link below to manage all user complaints.');
        $this->link = route('admin.complaints');
        $this->action = __('Manage Complaints');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
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
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
