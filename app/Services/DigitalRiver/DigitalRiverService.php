<?php

namespace App\Services\DigitalRiver;

use App\Models\ProductItem;
use App\Models\Refund;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use DigitalRiver\ApiSdk\ApiException as DrApiException;
use DigitalRiver\ApiSdk\Configuration as DrConfiguration;
use DigitalRiver\ApiSdk\Api\CheckoutsApi as DrCheckoutsApi;
use DigitalRiver\ApiSdk\Api\CustomersApi as DrCustomersApi;
use DigitalRiver\ApiSdk\Api\EventsApi as DrEventsApi;
use DigitalRiver\ApiSdk\Api\FileLinksApi as DrFileLinksApi;
use DigitalRiver\ApiSdk\Api\FulfillmentsApi as DrFulfillmentsApi;
use DigitalRiver\ApiSdk\Api\InvoicesApi as DrInvoicesApi;
use DigitalRiver\ApiSdk\Api\OrdersApi as DrOrdersApi;
use DigitalRiver\ApiSdk\Api\PlansApi as DrPlansApi;
use DigitalRiver\ApiSdk\Api\RefundsApi as DrRefundsApi;
use DigitalRiver\ApiSdk\Api\SourcesApi as DrSourcesApi;
use DigitalRiver\ApiSdk\Api\SubscriptionsApi as DrSubscriptionsApi;
use DigitalRiver\ApiSdk\Api\TaxIdentifiersApi as DrTaxIdentifiersApi;
use DigitalRiver\ApiSdk\Api\WebhooksApi as DrWebhooksApi;
use DigitalRiver\ApiSdk\Model\FileLink as DrFileLink;
use DigitalRiver\ApiSdk\Model\FileLinkRequest as DrFileLinkRequest;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\OrderRefund as DrOrderRefund;
use DigitalRiver\ApiSdk\Model\RefundItemRequest;
use DigitalRiver\ApiSdk\Model\RefundRequest as DrRefundRequest;
use DigitalRiver\ApiSdk\Model\WebhookUpdateRequest as DrWebhookUpdateRequest;
use Exception;


class DigitalRiverService
{
  /** @var Client $client */
  public $client = null;

  /** @var DrConfiguration DR configuration */
  public $config = null;

  /** @var DrOrdersApi|null */
  public $orderApi = null;

  /** @var DrRefundsApi|null */
  public $refundApi = null;

  /** @var DrEventsApi|null */
  public $eventApi = null;

  /** @var DrWebhooksApi|null */
  public $webhookApi = null;

  /** @var DrFileLinksApi|null */
  public $fileLinkApi = null;

  public function __construct()
  {
    // rest api client
    $this->client = new Client();

    // DR configuration
    $this->config = DrConfiguration::getDefaultConfiguration();
    $this->config->setAccessToken(config('dr.token'));
    $this->config->setHost(config('dr.host'));

    // DR apis
    $this->eventApi         = new DrEventsApi($this->client, $this->config);
    $this->orderApi         = new DrOrdersApi($this->client, $this->config);
    $this->refundApi        = new DrRefundsApi($this->client, $this->config);
    $this->webhookApi       = new DrWebhooksApi($this->client, $this->config);
    $this->fileLinkApi      = new DrfileLinksApi($this->client, $this->config);
  }

  protected function throwException(\Throwable $th, string $level = 'error'): Exception
  {
    if ($th instanceof DrApiException) {
      if ($th->getResponseObject()) {
        $message = $th->getResponseObject()->getErrors()[0]?->getMessage() ?? 'Unknown error';
      } else {
        $text = $th->getResponseBody() ?? $th->getMessage();
        $body = json_decode($text);
        $message = $body->errors[0]->message ?? $text;
      }
    } else {
      $text = $th->getMessage();
      $body = json_decode($text);
      $message = $body->errors[0]->message ?? $text;
    }
    Log::log($level, $th);
    return new Exception("{$message}", ($th->getCode() >= 100 && $th->getCode() < 600) ? $th->getCode() : 500);
  }

  /**
   * update & enable the default DrWebhook
   */
  public function updateDefaultWebhook(array $types, bool $enable)
  {
    try {
      $webhookUpdateRequest = new DrWebhookUpdateRequest();
      $webhookUpdateRequest->setTypes($types);
      $webhookUpdateRequest->setEnabled($enable);
      return $this->webhookApi->updateWebhooks(config('dr.default_webhook'), $webhookUpdateRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * get a DrOrder by id
   * @param string $id dr order id
   */
  public function getOrder(string $id): DrOrder
  {
    try {
      return $this->orderApi->retrieveOrders($id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * create a DrRefund
   */
  public function createRefund(Refund $refund): DrOrderRefund
  {
    $refundRequest = new DrRefundRequest();
    $refundRequest->setOrderId($refund->getDrOrderId());
    $refundRequest->setCurrency($refund->currency);
    $refundRequest->setReason($refund->reason ?? "");
    $refundRequest->setMetadata([
      'created_from' => 'siser-system',     // not created from dr portal or api directly
      'item_type' => $refund->item_type
    ]); // create from siser-system

    // item level refund or order level refund
    if ($refund->item_type == Refund::ITEM_LICENSE) {
      $item = $refund->items[0];
      if ($item['category'] != ProductItem::ITEM_CATEGORY_LICENSE) {
        throw new Exception('Invalid refund item category', 400);
      }
      $refundRequest->setItems([
        (new RefundItemRequest())
          ->setItemId($item['dr_item_id'])
          ->setQuantity($item['quantity'])
          ->setAmount($refund->amount)
      ]);
    } else {
      $refundRequest->setAmount($refund->amount);
    }

    try {
      return $this->refundApi->createRefunds($refundRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  /**
   * create file link from file id
   * @param string $fileId dr file id
   * @param Carbon $expiresTime link expires time
   */
  public function createFileLink(string $fileId, Carbon $expiresTime): DrFileLink
  {
    try {
      $fileLinkRequest = new DrFileLinkRequest();
      $fileLinkRequest->setFileId($fileId);
      $fileLinkRequest->setExpiresTime($expiresTime->toIso8601ZuluString()); // @phpstan-ignore-line

      return $this->fileLinkApi->createFileLinks($fileLinkRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }
}
