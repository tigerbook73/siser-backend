<?php

namespace App\Services\DigitalRiver;

use DigitalRiver\ApiSdk\Configuration as DrConfiguration;
use DigitalRiver\ApiSdk\Api\PayoutsApi as DrPayoutsApi;
use DigitalRiver\ApiSdk\Api\SalesSummariesApi as DrSalesSummariesApi;
use DigitalRiver\ApiSdk\Api\SalesTransactionsApi as DrSalesTransactionsApi;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * @template T
 * 
 * @param callable(): T $listApi
 * @return T
 */
function busyRetry(callable $listApi)
{
  while (true) {
    try {
      return $listApi();
    } catch (Exception $e) {
      if ($e->getCode() == 429) {
        Log::info("Too many requests, retrying in 5 seconds...\n");
        sleep(5);
        continue;
      }
      throw $e;
    }
  }
}


class DigitalRiverFinancialService
{
  /** @var Client $client */
  public $client = null;

  /** @var DrConfiguration DR configuration */
  public $config = null;

  /** @var DrPayoutsApi|null */
  public $payoutApi = null;

  /** @var DrSalesSummariesApi|null */
  public $salesSummaryApi = null;

  /** @var DrSalesTransactionsApi|null */
  public $salesTransactionApi = null;

  /**
   * first record
   */
  public $firstPayoutId = '2000661993_2215_2023';
  public $firstSalesSummaryId = '8100011183_2215_2023';
  public $firstSalesTransactionId = '1069552237_000010_3700576255';

  public function __construct()
  {
    // rest api client
    $this->client = new Client();

    // DR configuration
    $this->config = DrConfiguration::getDefaultConfiguration();
    $this->config->setAccessToken(config('dr.financial_token'));
    $this->config->setHost(config('dr.host'));

    // DR apis
    $this->payoutApi            = new DrPayoutsApi($this->client, $this->config);
    $this->salesSummaryApi      = new DrSalesSummariesApi($this->client, $this->config);
    $this->salesTransactionApi  = new DrSalesTransactionsApi($this->client, $this->config);
  }

  public function listPayout(...$args)
  {
    $args['limit'] = 100;
    return busyRetry(fn () => $this->payoutApi->listPayouts(...$args));
  }

  /**
   * List payouts since $since, by time based order. If $since is null, list the first one.
   */
  public function listPayoutSince(string $since = null)
  {
    if ($since) {
      return $this->listPayout(ending_before: $since);
    } else {
      return $this->listPayout(ids: $this->firstPayoutId)
        ->setHasMore(true);
    }
  }

  public function listSalesSummary(...$args)
  {
    $args['limit'] = 100;
    return busyRetry(fn () => $this->salesSummaryApi->listSalesSummaries(...$args));
  }

  /**
   * List salesSummarys since $since, by time based order. If $since is null, list the first one.
   */
  public function listSalesSummarySince(string $since = null)
  {
    if ($since) {
      return $this->listSalesSummary(ending_before: $since);
    } else {
      return $this->listSalesSummary(ids: $this->firstSalesSummaryId)
        ->setHasMore(true);
    }
  }


  public function listSalesTransaction(...$args)
  {
    $args['limit'] = 100;
    return busyRetry(fn () => $this->salesTransactionApi->listSalesTransactions(...$args));
  }



  /**
   * List salesTransactions since $since, by time based order. If $since is null, list the first one.
   */
  public function listSalesTransactionSince(string $since = null)
  {
    if ($since) {
      return busyRetry(fn () => $this->listSalesTransaction(ending_before: $since));
    } else {
      return busyRetry(fn () => $this->listSalesTransaction(ids: $this->firstSalesTransactionId))
        ->setHasMore(true);
    }
  }


  /** 
   * the following function are mainly for test or debug
   */

  /**
   * @param callable(string|null $starting_after): mixed $listApi
   */
  public function getTotalCount(callable $listApi)
  {
    $count = 0;
    $startingAfter = null;
    while (true) {
      $result = $listApi($startingAfter);
      $count += count($result->getData());
      if (count($result->getData()) > 0) {
        $startingAfter = $result->getData()[count($result->getData()) - 1]->getId();
      }
      if (!$result->getHasMore()) {
        break;
      }
    }

    return $count;
  }

  public function getPayoutCount()
  {
    return $this->getTotalCount(fn ($startingAfter) => $this->listPayout(starting_after: $startingAfter));
  }

  public function getSalesSummaryCount()
  {
    return $this->getTotalCount(fn ($startingAfter) => $this->listSalesSummary(starting_after: $startingAfter));
  }

  public function getSalesTransactionCount()
  {
    return $this->getTotalCount(fn ($startingAfter) => $this->listSalesTransaction(starting_after: $startingAfter));
  }
}
