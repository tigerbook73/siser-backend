<?php

namespace App\Services\Paddle;

use App\Models\DrEventRecord;
use App\Services\DigitalRiver\SubscriptionManagerResult;
use App\Services\DigitalRiver\WebhookException;
use App\Services\LicenseSharing\LicenseSharingService;
use App\Services\Paddle\PaddleService;
use DigitalRiver\ApiSdk\Model\Discount;
use Paddle\SDK\Entities\Event;
use Paddle\SDK\Notifications\Events\AdjustmentCreated;
use Paddle\SDK\Notifications\Events\AdjustmentUpdated;
use Paddle\SDK\Notifications\Events\PaymentMethodDeleted;
use Paddle\SDK\Notifications\Events\SubscriptionCreated;
use Paddle\SDK\Notifications\Events\SubscriptionUpdated;
use Paddle\SDK\Notifications\Events\TransactionCompleted;
use Paddle\SDK\Notifications\Events\TransactionPastDue;

class SubscriptionManagerPaddle
{
  public AddressService $addressService;
  public AdjustmentService $adjustmentService;
  public BusinessService $businessService;
  public CustomerService $customerService;
  public DiscountService $discountService;
  public PaymentMethodService $paymentMethodService;
  public PriceService $priceService;
  public ProductService $productService;
  public SubscriptionService $subscriptionService;
  public TransactionService $transactionService;

  /**
   * @var array<string, string>
   */
  public $eventHandlers = [];

  public function __construct(
    public PaddleService $paddleService,
    public LicenseSharingService $licenseService,
    public SubscriptionManagerResult $result,
  ) {
    $this->addressService       = new AddressService($this);
    $this->adjustmentService    = new AdjustmentService($this);
    $this->businessService      = new BusinessService($this);
    $this->customerService      = new CustomerService($this);
    $this->discountService      = new DiscountService($this);
    $this->paymentMethodService = new PaymentMethodService($this);
    $this->priceService         = new PriceService($this);
    $this->productService       = new ProductService($this);
    $this->subscriptionService  = new SubscriptionService($this);
    $this->transactionService   = new TransactionService($this);

    $this->eventHandlers = [
      // // customer events

      // address events

      // business events

      // payment method events
      'payment_method.deleted'          => 'onPaymentMethodDeleted',

      // subscription events
      'subscription.created'            => 'onSubscriptionCreated',
      'subscription.updated'            => 'onSubscriptionUpdated',

      // transaction events
      'transaction.completed'           => 'onTransactionCompleted',
      'transaction.past_due'            => 'onTransactionPastDue',

      // adjustment events
      'adjustment.created'              => 'onAdjustmentCreated',
      'adjustment.updated'              => 'onAdjustmentUpdated',
    ];
  }

  /**
   * webhook event
   */
  public function updateDefaultWebhook(bool $enable)
  {
    $this->paddleService->updateDefaultWebhook(array_keys($this->eventHandlers), $enable);
  }

  public function webhookHandler(array $eventRaw): \Illuminate\Http\JsonResponse
  {
    $event = Event::from($eventRaw);

    $this->result
      ->init(SubscriptionManagerResult::CONTEXT_WEBHOOK)
      ->setEventType($event->eventType)
      ->setEventId($event->notificationId);

    $eventHandler = $this->eventHandlers[$event->eventType->getValue()] ?? null;
    if (!$eventHandler || !method_exists($this, $eventHandler)) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_IGNORED, 'no-handler')
        ->appendMessage("event [{$this->result->getEventType()}] ignored: no-handler", location: __FUNCTION__);
      return response()->json($this->result->getData());
    }

    // find record
    $drEvent = DrEventRecord::fromDrEventIdOrNew($this->result->getEventId(), $this->result->getEventType());
    if ($drEvent->isCompleted()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_IGNORED, 'duplicated')
        ->appendMessage("event [{$this->result->getEventType()}] ignored: duplicated", location: __FUNCTION__,);
      return response()->json($this->result->getData());
    }
    if ($drEvent->isProcessing()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_FAILED, 'in-processing')
        ->appendMessage("event [{$this->result->getEventType()}] failed: already in processing", location: __FUNCTION__);
      return response()->json($this->result->getData(), 409); // return 409 to ask DR to retry
    }

    try {
      $drEvent->startProcessing();
      $this->result->appendMessage("event [{$this->result->getEventType()}] processing", location: __FUNCTION__);

      $this->$eventHandler($event);

      $this->result->appendMessage("event [{$this->result->getEventType()}] processed", location: __FUNCTION__);
      $drEvent->complete($this->result);

      return response()->json($this->result->getData());
    } catch (\Throwable $th) {
      if ($th instanceof WebhookException) {
        $this->result
          ->setResult(SubscriptionManagerResult::RESULT_FAILED, 'webhook-exception')
          ->appendMessage("event [{$this->result->getEventType()}] WebhookExcetpion: {$th->getMessage()}", location: __FUNCTION__, level: 'warning');
        $drEvent->fail($this->result);
      } else {
        $this->result
          ->setResult(SubscriptionManagerResult::RESULT_EXCEPTION, 'other-exception')
          ->appendMessage("event [{$this->result->getEventType()}] OtherException: {$th->getMessage()}", location: __FUNCTION__, level: 'error');
        $drEvent->fail($this->result);
      }
      return response()->json($this->result->getData(), 400);
    }
  }

  /**
   * trigger event
   */
  public function triggerEvent(string $notificationId, bool $force = false): string
  {
    if ($force) {
      /** @var ?DrEventRecord $drEvent */
      $drEvent = DrEventRecord::where('event_id', $notificationId)->first();
      if ($drEvent) {
        $drEvent->setStatus(DrEventRecord::STATUS_FAILED);
        $drEvent->setResolvedStatus(DrEventRecord::RESOLVE_STATUS_UNRESOLVED);
        $drEvent->setResolveComments('force to retry');
        $drEvent->save();
      }
    }
    return $this->paddleService->replayNotification($notificationId);
  }


  /**
   * Paddle event handler
   */

  /**
   * address event handler
   */

  /**
   * business event handler
   */

  /**
   * payment method event handler
   */
  public function onPaymentMethodDeleted(PaymentMethodDeleted $event)
  {
    $this->paymentMethodService->onPaymentMethodDeleted($event);
  }

  /**
   * subscription event handler
   */
  public function onSubscriptionCreated(SubscriptionCreated $event)
  {
    $this->subscriptionService->onSubscriptionCreated($event);
  }

  public function onSubscriptionUpdated(SubscriptionUpdated $event)
  {
    $this->subscriptionService->onSubscriptionUpdated($event);
  }

  /**
   * transaction event handler
   */
  public function onTransactionCompleted(TransactionCompleted $event)
  {
    $this->transactionService->onTransactionCompleted($event);
  }

  public function onTransactionPastDue(TransactionPastDue $event)
  {
    $this->transactionService->onTransactionPastDue($event);
  }

  /**
   * adjustment event handler
   */
  public function onAdjustmentCreated(AdjustmentCreated $event)
  {
    $this->adjustmentService->onAdjustmentCreated($event);
  }

  public function onAdjustmentUpdated(AdjustmentUpdated $event)
  {
    $this->adjustmentService->onAdjustmentUpdated($event);
  }
}
