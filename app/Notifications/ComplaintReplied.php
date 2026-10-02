<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintReplied extends Notification
{
    use Queueable;

    public function __construct(
        public Complaint $complaint,
        public string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Complaint update - '.$this->complaint->subject)
            ->line($this->message)
            ->action('View Complaint', url('/customer/complaints/'.$this->complaint->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'message' => 'Complaint update: '.$this->complaint->subject,
        ];
    }
}
