<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected mixed $site;

    public function __construct(protected string $name, protected string $email, protected string $subject, protected string $message)
    {
        $this->site = getSiteConfig('name');
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
            ->replyTo($this->email)
            ->subject('['.$this->site.'] '.$this->subject)
            ->line(__(':name just sent a message to the site administrators using the contact form at :site', [
                'name' => $this->name,
                'site' => $this->site,
            ]))
            ->line(__('Verbatim message is displayed below:'))
            ->line($this->message);
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
