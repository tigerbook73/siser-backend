<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;

class InvoicePDF extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * The subscription instance.
   *
   * @var \App\Models\Invoice
   */
  public $invoice;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct(Invoice $invoice)
  {
    $this->invoice = $invoice;
  }

  /**
   * Get the message envelope.
   *
   * @return \Illuminate\Mail\Mailables\Envelope
   */
  public function envelope()
  {
    return new Envelope(
      to: $this->invoice->user->billing_info->email,
      subject: 'Invoice PDF',
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
      view: 'emails.InvoicePDF',
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
