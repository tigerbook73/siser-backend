<?php

namespace App\Notifications;

use App\Models\Country;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public const NOTIF_ABORTED          = 'subscription.aborted';
  public const NOTIF_CANCELLED        = 'subscription.cancelled';
  public const NOTIF_CONFIRMED        = 'subscription.confirmed';
  public const NOTIF_EXTENDED         = 'subscription.extended';
  public const NOTIF_FAILED           = 'subscription.failed';
  public const NOTIF_INVOICE_PDF      = 'subscription.invoice-pdf';
  public const NOTIF_INVOICE_PENDING  = 'subscription.invoice-pending';
  public const NOTIF_REMINDER         = 'subscription.reminder';
  public const NOTIF_TERMINATED       = 'subscription.terminated';
  public const NOTIF_UPDATED          = 'subscription.updated';

  static public $types = [
    self::NOTIF_ABORTED               => ['subject' => "Subscription Aborted",        'view' => null],
    self::NOTIF_CANCELLED             => ['subject' => "Subscription Cancelled",      'view' => null],
    self::NOTIF_CONFIRMED             => ['subject' => "Subscription Confirmed",      'view' => null],
    self::NOTIF_EXTENDED              => ['subject' => "Subscription Extended",       'view' => null],
    self::NOTIF_FAILED                => ['subject' => "Subscription Failed",         'view' => null],
    self::NOTIF_INVOICE_PDF           => ['subject' => "Invoice PDF",                 'view' => null],
    self::NOTIF_INVOICE_PENDING       => ['subject' => "Subscription Payment Failed", 'view' => null],
    self::NOTIF_REMINDER              => ['subject' => "Subscription Renew Reminder", 'view' => null],
    self::NOTIF_TERMINATED            => ['subject' => "Subscription Terminated",     'view' => null],
    self::NOTIF_UPDATED               => ['subject' => "Subscription Updated",        'view' => null],
  ];

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(
    public string $type,
    public Subscription|null $subscription = null,
    public Invoice|null $invoice = null
  ) {
    if (!isset(static::$types[$type])) {
      throw new HttpException(400, 'Invalid SubscriptionNotification.type!');
    }
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function via($notifiable)
  {
    return $this->type ? ['mail'] : [];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail($notifiable)
  {
    $subject = static::$types[$this->type]['subject'];
    $view = static::$types[$this->type]['view'] ?? $this->type;
    return (new MailMessage)
      // ->from()
      ->subject($subject)
      ->view("emails.$view", [
        'subscription'  => $this->subscription,
        'invoice'       => $this->invoice,
        'timezone'      => $this->subscription->user->timezone,
      ]);
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
