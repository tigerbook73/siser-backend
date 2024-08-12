<?php

namespace App\Services\DigitalRiver;

use App\Models\SdrConfiguration;
use App\Models\SdrPayout;
use App\Models\SdrSalesSummary;
use App\Models\SdrSalesTransaction;
use Illuminate\Support\Facades\Log;

class FinancialService
{
  public DigitalRiverFinancialService $drService;

  public function __construct()
  {
    $this->drService = new DigitalRiverFinancialService();
  }

  /**
   * reset all financial data in DB
   */
  public function resetAll()
  {
    SdrConfiguration::where('name', 'like', '%.latest_id')->update(['value' => null]);

    SdrSalesTransaction::query()->delete();
    SdrSalesSummary::query()->delete();
    SdrPayout::query()->delete();
  }

  /**
   * full sync financial data from DR to DB
   */
  public function resyncAll()
  {
    $this->resetAll();
    $this->syncPayout();
    $this->syncSalesSummary();
    $this->syncSalesTransaction();
  }

  /**
   * incremental sync financial data from DR to DB and update related data
   */
  public function syncAll()
  {
    $payoutIds = $this->syncPayout();
    $salesSummaryIds = $this->syncSalesSummary();
    $this->syncSalesTransaction();

    $this->syncSalesSummaryByPayoutIds($payoutIds);
    $this->syncSalesTransactionBySummaryIds($salesSummaryIds);
  }

  /**
   * incremental sync sales transaction from DR to DB
   *
   * @param bool $byForce force to sync all data from DR
   * @return void
   */
  public function syncSalesTransaction(bool $byForce = false): void
  {
    /** @var SdrConfiguration $latest */
    $latest = SdrConfiguration::find('sales_transactions.latest_id');
    if ($byForce) {
      $latest->value = null;
      $latest->save();
    }

    while (true) {
      $result = $this->drService->listSalesTransactionSince(since: $latest->value);
      if (count($result->getData()) > 0) {
        // sync data
        $data = array_map(fn($record) => SdrSalesTransaction::dataFromDrObject($record), $result->getData());
        SdrSalesTransaction::upsert($data, 'id');
        Log::info('FIN_LOG: ' . count($data) . ' sales transactions synced');

        // update latest_id
        $latest->value = $data[0]['id'];
        $latest->save();
      }
      if (!$result->getHasMore()) {
        break;
      }
    }
  }

  /**
   * update sales transaction by sales summary ids (called by summary is created)
   *
   * @param string[] $salesSummaryIds
   * @return void
   */
  public function syncSalesTransactionBySummaryIds(array $salesSummaryIds)
  {
    foreach ($salesSummaryIds as $salesSummaryId) {
      $startingAfter = null;
      while (true) {
        $result = $this->drService->listSalesTransaction(sales_summary_id: $salesSummaryId, starting_after: $startingAfter);
        if (count($result->getData()) > 0) {
          // sync data
          $data = array_map(fn($record) => SdrSalesTransaction::dataFromDrObject($record), $result->getData());
          SdrSalesTransaction::upsert($data, 'id');
          Log::info('FIN_LOG: ' . count($data) . ' sales transactions updated (or synced)');

          // update cursor
          $startingAfter = $data[count($data) - 1]['id'];
        }
        if (!$result->getHasMore()) {
          break;
        }
      }
    }
  }

  /**
   * incremental sync sales summary from DR to DB
   *
   * @param bool $byForce force to sync all data from DR
   * @return string[] synchronized sales summary ids
   */
  public function syncSalesSummary(bool $byForce = false): array
  {
    /** @var SdrConfiguration $latest */
    $latest = SdrConfiguration::find('sales_summaries.latest_id');
    if ($byForce) {
      $latest->value = null;
      $latest->save();
    }

    $salesSummaryIds = [];
    while (true) {
      $result = $this->drService->listSalesSummarySince(since: $latest->value);
      if (count($result->getData()) > 0) {
        // sync data
        $data = array_map(fn($record) => SdrSalesSummary::dataFromDrObject($record), $result->getData());
        SdrSalesSummary::upsert($data, 'id');
        Log::info('FIN_LOG: ' . count($data) . ' sales summaries synced');

        // update latest_id
        $latest->value = $data[0]['id'];
        $latest->save();

        // collect sales summary ids
        $salesSummaryIds = array_merge($salesSummaryIds, array_map(fn($record) => $record['id'], $data));
      }
      if (!$result->getHasMore()) {
        break;
      }
    }
    return $salesSummaryIds;
  }

  /**
   * update sales summary by payout ids (called by payout is created)
   *
   * @param string[] $payoutIds
   * @return void
   */
  public function syncSalesSummaryByPayoutIds(array $payoutIds)
  {
    foreach ($payoutIds as $payoutId) {
      $startingAfter = null;
      while (true) {
        $result = $this->drService->listSalesSummary(payout_id: $payoutId, starting_after: $startingAfter);
        if (count($result->getData()) > 0) {
          // sync data
          $data = array_map(fn($record) => SdrSalesSummary::dataFromDrObject($record), $result->getData());
          SdrSalesSummary::upsert($data, 'id');
          Log::info('FIN_LOG: ' . count($data) . ' sales summaries updated (or synced)');

          // update cursor
          $startingAfter = $data[count($data) - 1]['id'];
        }
        if (!$result->getHasMore()) {
          break;
        }
      }
    }
  }

  /**
   * incremental sync payouts from DR to DB
   *
   * @param bool $byForce force to sync all data from DR
   * @return string[] synchronized payout ids
   */
  public function syncPayout(bool $byForce = false): array
  {
    /** @var SdrConfiguration $latest */
    $latest = SdrConfiguration::find('payouts.latest_id');
    if ($byForce) {
      $latest->value = null;
      $latest->save();
    }

    $payoutIds = [];
    while (true) {
      $result = $this->drService->listPayoutSince(since: $latest->value);
      if (count($result->getData()) > 0) {
        // sync data
        $data = array_map(fn($record) => SdrPayout::dataFromDrObject($record), $result->getData());
        SdrPayout::upsert($data, 'id');
        Log::info('FIN_LOG: ' . count($data) . ' sales payouts synced');

        // update latest_id
        $latest->value = $data[0]['id'];
        $latest->save();

        // collect payout ids
        $payoutIds = array_merge($payoutIds, array_map(fn($record) => $record['id'], $data));
      }
      if (!$result->getHasMore()) {
        break;
      }
    }
    return $payoutIds;
  }
}
