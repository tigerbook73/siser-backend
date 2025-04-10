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

  public const NOTIF_WELCOME_BACK_FOR_RENEW       = 'subscription.welcome-back.for-renew';
  public const NOTIF_WELCOME_BACK_FOR_FAILED      = 'subscription.welcome-back.for-failed';

  static public $types = [
    self::NOTIF_WELCOME_BACK_FOR_RENEW        => ['subject' => "Welcome Back Package",                    'validate' => null],
    self::NOTIF_WELCOME_BACK_FOR_FAILED       => ['subject' => "Subscription ##S Terminated",             'validate' => null],
  ];

  public Subscription $subscription;
  public ?Invoice $invoice;
  public ?Refund $refund;
  public ?string $credit_memo; // credit memo url

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
      locale: $this->subscription->getBillingInfo()->locale,
      timezone: $this->subscription->user->timezone,
      country: $this->subscription->getBillingInfo()->address->country,
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
