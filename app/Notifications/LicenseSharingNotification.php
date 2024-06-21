<?php

namespace App\Notifications;

use App\Models\LicenseSharingInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LicenseSharingNotification extends Notification implements ShouldQueue
{
  use Queueable;

  // GUEST NOTIFICATIONS
  public const NOTIF_NEW_INVITATION       = 'license-sharing.new-invitation';
  public const NOTIF_INVITATION_EXPIRED   = 'license-sharing.invitation-expired';
  public const NOTIF_INVITATION_CANCELLED = 'license-sharing.invitation-cancelled';
  public const NOTIF_INVITATION_REVOKED   = 'license-sharing.invitation-revoked';

  static public $types = [
    self::NOTIF_NEW_INVITATION        => ['subject' => "New license-sharing invitation"],
    self::NOTIF_INVITATION_EXPIRED    => ['subject' => "License-sharing expired"],
    self::NOTIF_INVITATION_CANCELLED  => ['subject' => "License-sharing cancelled"],
    self::NOTIF_INVITATION_REVOKED    => ['subject' => "License-sharing revoked by owner"],
  ];

  public EmailHelper $helper;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(
    public string $type,
    public LicenseSharingInvitation $invitation,
  ) {
    if (!isset(static::$types[$type])) {
      throw new HttpException(400, 'Invalid LicenseSharingNotification.type!');
    }

    $this->helper = new EmailHelper(
      locale: 'en-US',
      timezone: $this->invitation->guest->timezone,
      country: 'US',
      currency: 'USD',
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

    $subject = static::$types[$this->type]['subject'];
    $view = static::$types[$this->type]['view'] ?? $this->type;
    return (new MailMessage)
      ->subject($subject)
      ->bcc(config('siser.bcc_email'))
      ->view("emails.$view", [
        'type'            => $this->type,
        'invitation'      => $this->invitation,
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
