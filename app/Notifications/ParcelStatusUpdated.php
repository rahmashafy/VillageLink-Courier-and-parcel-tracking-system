<?php

namespace App\Notifications;

use App\Models\Parcel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParcelStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public Parcel $parcel,
        public string $statusLabel,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Parcel '.$this->parcel->tracking_id.' - '.$this->statusLabel)
            ->line('Your parcel status has been updated to: '.$this->statusLabel)
            ->line('Tracking ID: '.$this->parcel->tracking_id)
            ->action('Track Parcel', url('/customer/parcels/'.$this->parcel->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tracking_id' => $this->parcel->tracking_id,
            'status' => $this->parcel->status,
            'message' => 'Parcel '.$this->parcel->tracking_id.' is now: '.$this->statusLabel,
        ];
    }
}
