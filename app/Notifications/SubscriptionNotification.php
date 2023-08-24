<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Refund;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public const NOTIF_ORDER_ABORTED              = 'subscription.order-aborted';
  public const NOTIF_ORDER_CANCELLED            = 'subscription.order-cancelled';
  public const NOTIF_ORDER_CONFIRMED            = 'subscription.order-confirmed';
  public const NOTIF_ORDER_CREDIT_MEMO          = 'subscription.order-credit-memo';
  public const NOTIF_ORDER_INVOICE              = 'subscription.order-invoice';
  public const NOTIF_ORDER_REFUND_FAILED        = 'subscription.order-refund-failed';
  public const NOTIF_ORDER_REFUNDED             = 'subscription.order-refunded';

  public const NOTIF_CANCELLED                  = 'subscription.cancelled';
  public const NOTIF_CANCELLED_REFUND           = 'subscription.cancelled-refund';
  public const NOTIF_EXTENDED                   = 'subscription.extended';
  public const NOTIF_FAILED                     = 'subscription.failed';
  public const NOTIF_INVOICE_PENDING            = 'subscription.invoice-pending';
  public const NOTIF_REMINDER                   = 'subscription.reminder';
  public const NOTIF_TERMINATED                 = 'subscription.terminated';
  public const NOTIF_TERMS_CHANGED              = 'subscription.terms-changed';

  // TODO: implement these
  public const NOTIF_FREE_TRIAL_ENDED           = 'subscription.free-trial-ended';
  public const NOTIF_COUPON_ENDED               = 'subscription.coupone-ended';

  static public $types = [
    self::NOTIF_ORDER_ABORTED         => ['subject' => "Order Aborted"],
    self::NOTIF_ORDER_CANCELLED       => ['subject' => "Order Cancelled"],
    self::NOTIF_ORDER_CONFIRMED       => ['subject' => "Order Confirmed"],
    self::NOTIF_ORDER_CREDIT_MEMO     => ['subject' => "Order Credit Memo"],
    self::NOTIF_ORDER_INVOICE         => ['subject' => "Order Invoice PDF"],
    self::NOTIF_ORDER_REFUND_FAILED   => ['subject' => "Order Refund Failed"],
    self::NOTIF_ORDER_REFUNDED        => ['subject' => "Order Refund Confirmed"],

    self::NOTIF_CANCELLED             => ['subject' => "Subscription Cancelled"],
    self::NOTIF_CANCELLED_REFUND      => ['subject' => "Subscription Cancelled & Terminated"],
    self::NOTIF_EXTENDED              => ['subject' => "Subscription Extended"],
    self::NOTIF_FAILED                => ['subject' => "Subscription Failed"],
    self::NOTIF_INVOICE_PENDING       => ['subject' => "Subscription Payment Failed"],
    self::NOTIF_REMINDER              => ['subject' => "Subscription Renew Reminder"],
    self::NOTIF_TERMINATED            => ['subject' => "Subscription Terminated"],
    self::NOTIF_TERMS_CHANGED         => ['subject' => "Subscription Terms Changed"],
  ];

  public Subscription $subscription;
  public Invoice|null $invoice;
  public Refund|null $refund;

  public EmailHelper $helper;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(
    public string $type,
    array $context  = []
  ) {
    if (!isset(static::$types[$type])) {
      throw new HttpException(400, 'Invalid SubscriptionNotification.type!');
    }

    $this->subscription     = $context['subscription'];
    $this->invoice          = $context['invoice'] ?? null;
    $this->refund           = $context['refund'] ?? null;

    $this->helper           = new EmailHelper(
      locale: $this->subscription->billing_info['locale'],
      timezone: $this->subscription->user->timezone,
      country: $this->subscription->billing_info['address']['country'],
      currency: $this->subscription->currency,
    );
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
      ->bcc(config('siser.bcc_email'))
      ->view("emails.$view", [
        'type'            => $this->type,
        'subscription'    => $this->subscription,
        'invoice'         => $this->invoice,
        'refund'          => $this->refund,
        'helper'          => $this->helper,
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
