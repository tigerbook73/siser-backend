<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\StatisticRecord;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticRecordService
{
  /**
   * build records from subscription logs
   */
  public function rebuildSubscriptionLogs(): array
  {
    // this is one-time job
    SubscriptionLog::truncate();

    $activated = 0;
    $cancelled = 0;
    $converted = 0;
    $extended = 0;
    $failed = 0;
    $stopped = 0;
    Subscription::where('subscription_level', '>=', 2)
      ->whereNotNull('start_date')
      ->whereIn('status', [
        Subscription::STATUS_ACTIVE,
        Subscription::STATUS_STOPPED,
        Subscription::STATUS_FAILED
      ])
      ->chunkById(100, function ($subscriptions) use (&$activated, &$cancelled, &$converted, &$extended, &$failed, &$stopped) {
        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
          // log activated
          SubscriptionLog::logEvent(
            SubscriptionLog::SUBSCRIPTION_ACTIVATED,
            $subscription,
            $subscription->start_date
          );
          $activated++;

          // log cancelled (cancelling)
          if ($subscription->sub_status == Subscription::SUB_STATUS_CANCELLING) {
            SubscriptionLog::logEvent(
              SubscriptionLog::SUBSCRIPTION_CANCELLED,
              $subscription,
              $subscription->current_period_start_date->addSecond() // can not get accurate cancelled date
            );
            $cancelled++;
          }

          // log extended & converted
          if ($subscription->current_period > 1) {
            /** @var Invoice[] $invoices */
            $invoices = $subscription->invoices()
              ->where('period', '>=', 1)
              ->whereIn(
                'status',
                [
                  Invoice::STATUS_COMPLETED,
                  Invoice::STATUS_REFUNDED,
                  Invoice::STATUS_PARTLY_REFUNDED,
                  Invoice::STATUS_REFUNDING
                ]
              )
              ->orderBy('period')
              ->get();
            for ($index = 1; $index < count($invoices); $index++) {
              if ($index == 1 && ($invoices[0]->coupon_info['discount_type'] ?? null) == 'free_trial') {
                SubscriptionLog::logEvent(
                  SubscriptionLog::SUBSCRIPTION_CONVERTED,
                  $subscription,
                  $invoices[$index]->invoice_date
                );
                $converted++;
              }
              SubscriptionLog::logEvent(
                SubscriptionLog::SUBSCRIPTION_EXTENDED,
                $subscription,
                $invoices[$index]->invoice_date
              );
              $extended++;
              continue;
            }
          }

          // log failed
          if ($subscription->end_date && $subscription->getStatus() == Subscription::STATUS_FAILED) {
            SubscriptionLog::logEvent(
              SubscriptionLog::SUBSCRIPTION_FAILED,
              $subscription,
              $subscription->end_date
            );
            $failed++;
          }

          // log stopped & cancelled
          if ($subscription->end_date && $subscription->getStatus() == Subscription::STATUS_STOPPED) {
            if ($subscription->stop_reason && strpos($subscription->stop_reason, 'cancel') !== false) {
              SubscriptionLog::logEvent(
                SubscriptionLog::SUBSCRIPTION_CANCELLED,
                $subscription,
                $subscription->current_period_start_date->addSecond() // can not get accurate cancelled date
              );
              $cancelled++;
            }

            SubscriptionLog::logEvent(
              SubscriptionLog::SUBSCRIPTION_STOPPED,
              $subscription,
              $subscription->end_date
            );
            $stopped++;
          }
        }
      });

    return [
      'activated'     => $activated,
      'cancelled'     => $cancelled,
      'converted'     => $converted,
      'extended'      => $extended,
      'failed'        => $failed,
      'stopped'       => $stopped,
    ];
  }

  public function resetRecords()
  {
    StatisticRecord::truncate();
  }

  public function generateRecords()
  {
    // step 1: get last record date
    if ($lastRecord = StatisticRecord::orderBy('date', 'desc')->first()) {
      $startDate = $lastRecord->date->addDay();
    } else {
      $startDate = new Carbon('2022-10-17');
    }
    $endDate   = Carbon::yesterday();

    for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
      $statistic = new StatisticRecord();
      $statistic->date = $date->clone();

      $classfiedRecords = [];

      // statistic data from DB (total count)
      $totalRecords = $this->querySubscriptionRecords($date);
      foreach ($totalRecords as $record) {
        $record->coupon = $record->coupon ?? 'none';
        $key = "{$record->country}-{$record->currency}-{$record->subscription_level}-{$record->plan}-{$record->coupon}-{$record->machine_owner}";
        $classfiedRecords[$key] = (array)$record;

        $classfiedRecords[$key]['activated']  = 0;
        $classfiedRecords[$key]['cancelled']  = 0;
        $classfiedRecords[$key]['converted']  = 0;
        $classfiedRecords[$key]['extended']   = 0;
        $classfiedRecords[$key]['failed']     = 0;
        $classfiedRecords[$key]['stopped']    = 0;
      }

      /** @var SubscriptionLog[] $logs */
      $logs = SubscriptionLog::where('date', $date)->get();
      foreach ($logs as $log) {
        $subscriptionInfo = $log->data['subscription'];
        $country = $subscriptionInfo['billing_info']['address']['country'];
        $currency = $subscriptionInfo['currency'];
        $subscription_level = $subscriptionInfo['subscription_level'];
        $plan = $subscriptionInfo['plan_info']['interval'];
        $coupon = $subscriptionInfo['coupon_info']['discount_type'] ?? 'none';
        $machine_owner = $log->data['user']['machine_count'] > 0 ? 1 : 0;

        $key = "{$country}-{$currency}-{$subscription_level}-{$plan}-{$coupon}-{$machine_owner}";

        // update classified records with log's data
        if (!isset($classfiedRecords[$key])) {
          $classfiedRecords[$key]['country'] = $country;
          $classfiedRecords[$key]['currency'] = $currency;
          $classfiedRecords[$key]['subscription_level'] = $subscription_level;
          $classfiedRecords[$key]['plan'] = $plan;
          $classfiedRecords[$key]['coupon'] = $coupon;
          $classfiedRecords[$key]['machine_owner'] = $machine_owner;
          $classfiedRecords[$key]['count'] = 0;

          $classfiedRecords[$key]['activated']  = 0;
          $classfiedRecords[$key]['cancelled']  = 0;
          $classfiedRecords[$key]['converted']  = 0;
          $classfiedRecords[$key]['extended']   = 0;
          $classfiedRecords[$key]['failed']     = 0;
          $classfiedRecords[$key]['stopped']    = 0;
        }

        if ($log->event == SubscriptionLog::SUBSCRIPTION_ACTIVATED) {
          $classfiedRecords[$key]['activated']++;
        } else if ($log->event == SubscriptionLog::SUBSCRIPTION_CANCELLED) {
          $classfiedRecords[$key]['cancelled']++;
        } else if ($log->event == SubscriptionLog::SUBSCRIPTION_CONVERTED) {
          $classfiedRecords[$key]['converted']++;
        } else if ($log->event == SubscriptionLog::SUBSCRIPTION_EXTENDED) {
          $classfiedRecords[$key]['extended']++;
        } else if ($log->event == SubscriptionLog::SUBSCRIPTION_FAILED) {
          $classfiedRecords[$key]['failed']++;
        } else if ($log->event == SubscriptionLog::SUBSCRIPTION_STOPPED) {
          $classfiedRecords[$key]['stopped']++;
        }
      }

      $statisticRecord = [];
      foreach ($classfiedRecords as $record) {
        $statisticRecord[] = $record;
      }

      $statistic->record = $statisticRecord;
      $statistic->save();
    }
  }


  /**
   * query subscription classified statistic records from from DB
   * 
   * @param string $date "<= Date($date)"
   * @return array
   *  [
   *    [
   *      'country' => string,
   *      'currency' => string,
   *      'plan' => string,
   *      'coupon' => string,
   *      'machine_owner' => bool,
   *      'count' => int,
   *    ]
   *  ]
   */
  public function querySubscriptionRecords($date): array
  {
    $date = Carbon::parse($date)->startOfDay()->addDay()->toDateString();

    $sql = "
      SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(`subscriptions`.`billing_info`, '$.\"address\".\"country\"')) AS `country`,
        `subscriptions`.`currency`,
        `subscriptions`.`subscription_level`,
        JSON_UNQUOTE(JSON_EXTRACT(`subscriptions`.`plan_info`, '$.\"interval\"')) AS `plan`,
        CASE
          WHEN `subscriptions`.`subscription_level` > 1 THEN JSON_UNQUOTE(JSON_EXTRACT(`subscriptions`.`coupon_info`, '$.\"discount_type\"'))
          ELSE 'basic'
        END AS `coupon`,
        CASE
          WHEN `users`.`machine_count` > 0 THEN 'owner'
          ELSE 'non-owner'
        END AS `machine_owner`,
        COUNT('*') AS `count`
      FROM
        `subscriptions`
      JOIN `users` ON `subscriptions`.`user_id` = `users`.`id`
      WHERE
        `subscriptions`.`subscription_level` > 0
        and `subscriptions`.`start_date` < '{$date}'
        and (`subscriptions`.`end_date` IS NULL or `subscriptions`.`end_date` >= '{$date}')
      GROUP BY `country`, `currency`, `subscription_level`, `plan`, `coupon`, `machine_owner`;";

    return DB::select($sql);
  }
}
