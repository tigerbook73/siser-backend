<?php

namespace App\Services\DigitalRiver;

use App\Models\Invoice;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\TaxId;
use App\Models\User;

class SubscriptionManagerResult
{
  const CONTEXT_API       = 'api';
  const CONTEXT_WEBHOOK   = 'webhook';

  const RESULT_INIT       = 'init';           // before process
  const RESULT_PROCESSED  = 'success';        // processed successfully
  const RESULT_IGNORED    = 'ignored';        // no handler or duplicated event (event handler of the event type is not invoked)
  const RESULT_SKIPPED    = 'skipped';        // event handler of the event type is invoked but no action is taken
  const RESULT_FAILED     = 'failed';         // failed to process, warning message is logged.
  const RESULT_EXCEPTION  = 'exception';      // exception occured, error message is logged.

  const DATA_RESULT           = 'result';
  const DATA_EVENT_ID         = 'event_id';
  const DATA_EVENT_TYPE       = 'event_type';
  const DATA_USER_ID          = 'user_id';
  const DATA_SUBSCRIPTION_ID  = 'subscription_id';
  const DATA_INVOICE_ID       = 'invoice_id';
  const DATA_REFUND_ID        = 'refund_id';
  const DATA_TAX_ID           = 'tax_id';

  public string $contextType;
  public string $result;

  /**
   * General data
   * @var array{
   *    result            : string,
   *    event_id?         : string|null,
   *    event_type?       : string|null,
   *    user_id?          : int|null,
   *    subscription_id?  : int|null,
   *    invoice_id?       : int|null,
   *    refund_id?        : int|null,
   *    tax_id?           : int|null,
   * } $data
   */

  public array $data;

  // message
  public array $messages;

  public function __construct(string $contextType = self::CONTEXT_API)
  {
    $this->init($contextType);
  }

  /**
   * set member to default value
   */
  public function init(string $contextType = self::CONTEXT_API): self
  {
    $this->contextType    = $contextType;
    $this->result         = self::RESULT_INIT;
    $this->data           = [
      self::DATA_RESULT => self::RESULT_INIT,
    ];
    $this->messages       = [];
    return $this;
  }

  public function setResult(string $result): self
  {
    $this->result = $result;
    $this->data[self::DATA_RESULT] = $result;
    return $this;
  }

  public function getResult(): string
  {
    return $this->result;
  }

  public function setEventId(string $eventId): self
  {
    $this->data[self::DATA_EVENT_ID] = $eventId;
    return $this;
  }

  public function getEventId(): ?string
  {
    return $this->data[self::DATA_EVENT_ID] ?? null;
  }

  public function setEventType(string $eventType): self
  {
    $this->data[self::DATA_EVENT_TYPE] = $eventType;
    return $this;
  }

  public function getEventType(): ?string
  {
    return $this->data[self::DATA_EVENT_TYPE] ?? null;
  }

  public function getUserId(): ?int
  {
    return $this->data[self::DATA_USER_ID] ?? null;
  }

  public function setSubscription(Subscription $subscription): self
  {
    $this->data[self::DATA_USER_ID]           = $subscription->user_id;
    $this->data[self::DATA_SUBSCRIPTION_ID]   = $subscription->id;
    return $this;
  }

  public function getSubscriptionId(): ?int
  {
    return $this->data[self::DATA_SUBSCRIPTION_ID] ?? null;
  }

  public function setInvoice(Invoice $invoice): self
  {
    $this->data[self::DATA_USER_ID]         = $invoice->user_id;
    $this->data[self::DATA_SUBSCRIPTION_ID] = $invoice->subscription_id;
    $this->data[self::DATA_INVOICE_ID]      = $invoice->id;
    return $this;
  }

  public function setRefund(Refund $refund): self
  {
    $this->data[self::DATA_USER_ID]         = $refund->user_id;
    $this->data[self::DATA_SUBSCRIPTION_ID] = $refund->subscription_id;
    $this->data[self::DATA_INVOICE_ID]      = $refund->invoice_id;
    $this->data[self::DATA_REFUND_ID]       = $refund->id;
    return $this;
  }

  public function setTaxId(TaxId $taxId): self
  {
    $this->data[self::DATA_USER_ID]         = $taxId->user_id;
    $this->data[self::DATA_TAX_ID]          = $taxId->id;
    return $this;
  }

  public function getData(): array
  {
    return array_filter($this->data, fn ($value) => $value !== null);
  }

  public function appendMessage(string $message, array $extra = [], string $location = '', string $level = 'info'): self
  {
    $formatedMessage = now()->format('[Y-m-d H:i:s v] : ') .
      ($location ? "[$location] : " : '') .
      $message .
      ($extra ? ' : ' . json_encode($extra) : '');
    $this->messages[] = $formatedMessage;

    DrLog::$level($location, $message, array_merge($this->getData(), $extra));
    return $this;
  }

  public function getMessages(): array
  {
    return $this->messages;
  }

  public function getLastMessage(): string
  {
    return $this->messages[count($this->messages) - 1] ?? "";
  }
}
