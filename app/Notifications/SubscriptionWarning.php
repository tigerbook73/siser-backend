<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;


class SubscriptionWarning extends Notification implements ShouldQueue
{
  use Queueable;

  public const NOTIF_LONG_PENDING_SUBSCRIPTION    = 'long-pending-subscription';

  /**
   * long period warning cooling periods
   */
  const INVOICE_PENDING_PERIOD = '30 minutes';
  const INVOICE_RENEW_PERIOD = '4 days';
  const INVOICE_PROCESSING_PERIOD = '2 days';
  const REFUND_PROCESSING_PERIOD = '3 days';

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(
    public string $type,
    public array $data
  ) {
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
    $message = (new MailMessage)
      ->subject("Subscription warning: $this->type")
      ->greeting('Hello!')
      ->line('There are warning on "' . $this->type . '"!');

    foreach ($this->data as $objType => $objIds) {
      $message->line("$objType : " . implode(', ', $objIds));
    }

    return $message;
  }

  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toArray($notifiable)
  {
    return $this->data;
  }

  static public function notify(string $type, array $data)
  {
    (new Developer)->notify(new SubscriptionWarning($type, $data));
  }
}
