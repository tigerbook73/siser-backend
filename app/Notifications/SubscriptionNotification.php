<?php

namespace App\Notifications;

use App\Mail\InvalidNotification;
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
  public const NOTIF_LAPSED                     = 'subscription.lapsed';
  public const NOTIF_REMINDER                   = 'subscription.reminder';
  public const NOTIF_RENEW_REQUIRED             = 'subscription.renew-required';
  public const NOTIF_RENEW_REQ_CONFIRMED        = 'subscription.renew-req-confirmed';
  public const NOTIF_RENEW_EXPIRED              = 'subscription.renew-expired';
  public const NOTIF_SOURCE_INVALID             = 'subscription.source-invalid';
  public const NOTIF_TERMINATED                 = 'subscription.terminated';
  public const NOTIF_TERMS_CHANGED              = 'subscription.terms-changed';

  // the following notification is once off and can be removed when done!
  public const NOTIF_PLAN_UPDATED_GERMAN        = 'subscription.plan-updated-german';
  public const NOTIF_PLAN_UPDATED_OTHER         = 'subscription.plan-updated-other';

  static public $types = [
    self::NOTIF_ORDER_ABORTED         => ['subject' => "Order ##O Aborted",                       'validate' => null],
    self::NOTIF_ORDER_CANCELLED       => ['subject' => "Order ##O Cancelled",                     'validate' => null],
    self::NOTIF_ORDER_CONFIRMED       => ['subject' => "Order ##O Confirmed",                     'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
    self::NOTIF_ORDER_CREDIT_MEMO     => ['subject' => "Order ##O Credit Memo",                   'validate' => null],
    self::NOTIF_ORDER_INVOICE         => ['subject' => "Order ##O Invoice PDF",                   'validate' => null],
    self::NOTIF_ORDER_REFUND_FAILED   => ['subject' => "Order ##O Refund Failed",                 'validate' => null],
    self::NOTIF_ORDER_REFUNDED        => ['subject' => "Order ##O Refund Confirmed",              'validate' => null],

    self::NOTIF_CANCELLED             => ['subject' => "Subscription ##S Cancelled",              'validate' => null],
    self::NOTIF_CANCELLED_REFUND      => ['subject' => "Subscription ##S Cancelled & Terminated", 'validate' => null],
    self::NOTIF_EXTENDED              => ['subject' => "Subscription ##S Extended",               'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
    self::NOTIF_FAILED                => ['subject' => "Subscription ##S Failed",                 'validate' => null],
    self::NOTIF_INVOICE_PENDING       => ['subject' => "Subscription ##S Payment Failed",         'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
    self::NOTIF_LAPSED                => ['subject' => "Subscription ##S Failed",                 'validate' => null],
    self::NOTIF_REMINDER              => ['subject' => "Subscription ##S Renew Reminder",         'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
    self::NOTIF_RENEW_REQUIRED        => ['subject' => "Subscription ##S Renew Required",         'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
    self::NOTIF_RENEW_REQ_CONFIRMED   => ['subject' => "Subscription ##S Renew Request Confirmed", 'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
    self::NOTIF_RENEW_EXPIRED         => ['subject' => "Subscription ##S Renew Expired",          'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
    self::NOTIF_SOURCE_INVALID        => ['subject' => "Subscription ##S Payment Method Invalid", 'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
    self::NOTIF_TERMINATED            => ['subject' => "Subscription ##S Terminated",             'validate' => null],
    self::NOTIF_TERMS_CHANGED         => ['subject' => "Subscription ##S Terms Changed",          'validate' => null],

    // the following notification is once off and can be removed when done!
    self::NOTIF_PLAN_UPDATED_GERMAN   => ['subject' => "Subscription ##S Plan Updated",           'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
    self::NOTIF_PLAN_UPDATED_OTHER    => ['subject' => "Subscription ##S Plan Updated",           'validate' => ['status' => [Subscription::STATUS_ACTIVE]]],
  ];

  public Subscription $subscription;
  public Invoice|null $invoice;
  public Refund|null $refund;
  public string|null $credit_memo; // credit memo url

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
    $this->credit_memo      = $context['credit_memo'] ?? null;

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
   * @return \Illuminate\Notifications\Messages\MailMessage|\Illuminate\Mail\Mailable
   */
  public function toMail($notifiable)
  {
    if ($validate = static::$types[$this->type]['validate'] ?? null) {
      if (!in_array($this->subscription->status, $validate['status'])) {
        return new InvalidNotification($this->subscription, $this->type);
      }
    }

    $subject = static::$types[$this->type]['subject'];
    $subject = str_replace('##S', '#' . $this->subscription->id, $subject);
    $subject = str_replace('##O', '#' . ($this->invoice?->id ?? '#'), $subject);
    $view = static::$types[$this->type]['view'] ?? $this->type;
    return (new MailMessage)
      ->subject($subject)
      ->bcc(config('siser.bcc_email'))
      ->view("emails.$view", [
        'type'            => $this->type,
        'subscription'    => $this->subscription,
        'invoice'         => $this->invoice,
        'refund'          => $this->refund,
        'credit_memo'     => $this->credit_memo,
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
