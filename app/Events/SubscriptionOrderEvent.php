<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\Refund;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionOrderEvent
{
  use Dispatchable, SerializesModels;

  const TYPE_ORDER_CONFIRMED    = 'order-confirmed';
  const TYPE_ORDER_REFUNDED     = 'order-refunded';


  /**
   * Create a new event instance.
   * 
   * @return void
   */
  public function __construct(public $type, public Invoice $invoice, public Refund|null $refund = null)
  {
    //
  }
}
