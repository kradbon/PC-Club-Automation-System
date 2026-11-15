<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking, public string $status) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'status' => $this->status,
            'message' => trans('ui.booking_notification_'.$this->status, [], $notifiable->preferred_locale ?: 'en'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable->preferred_locale ?: 'en';

        return (new MailMessage)
            ->subject(trans('ui.booking_mail_subject', [], $locale))
            ->line(trans('ui.booking_notification_'.$this->status, [], $locale))
            ->action(trans('ui.my_account', [], $locale), route('account.dashboard', ['locale' => $locale]));
    }
}
