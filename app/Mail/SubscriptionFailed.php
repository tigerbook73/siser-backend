<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;

class SubscriptionFailed extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * The subscription instance.
   *
   * @var \App\Models\Subscription
   */
  public $subscription;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct(Subscription $subscription)
  {
    $this->subscription = $subscription;
  }

  /**
   * Get the message envelope.
   *
   * @return \Illuminate\Mail\Mailables\Envelope
   */
  public function envelope()
  {
    return new Envelope(
      to: [$this->subscription->user->billing_info->email],
      subject: 'Subscription Failed',
    );
  }

  /**
   * Get the message content definition.
   *
   * @return \Illuminate\Mail\Mailables\Content
   */
  public function content()
  {
    return new Content(
      view: 'emails.subscription-failed',
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array
   */
  public function attachments()
  {
    return [];
  }
}
