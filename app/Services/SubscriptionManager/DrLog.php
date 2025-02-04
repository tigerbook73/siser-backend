<?php

namespace App\Services\SubscriptionManager;

use App\Models\Invoice;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * DrLog class
 */
class DrLog
{
  static public function log(string $level, string $location, string $action, Subscription|Invoice|User|Refund|array $context = [])
  {
    if ($context instanceof Subscription) {
      $context = [
        'subscription_id' => $context->id,
        'subscription_status' => $context->status
      ];
    } else if ($context instanceof Invoice) {
      $context = [
        'subscription_id' => $context->subscription_id,
        'invoice_id' => $context->id,
        'invoice_status' => $context->status
      ];
    } else if ($context instanceof Refund) {
      $context = [
        'subscription_id' => $context->subscription_id,
        'invoice_id' => $context->invoice_id,
        'refund_id' => $context->id,
        'refund_status' => $context->status
      ];
    } else if ($context instanceof User) {
      $context = [
        'user_id' => $context->id,
        'subscription_level' => $context->subscription_level
      ];
    }
    Log::log($level, 'DR_LOG: ' . $location . ': ' . $action . ($context ? ':' : ''), $context);
  }

  static public function info(string $location, string $action, Subscription|Invoice|User|array $context = [])
  {
    // level == __FUNCTION__, same the other functions
    self::log(__FUNCTION__, $location, $action, $context);
  }

  static public function warning(string $location, string $action, Subscription|Invoice|User|array $context = [])
  {
    self::log(__FUNCTION__, $location, $action, $context);
  }

  static public function error(string $location, string $action, Subscription|Invoice|User|array $context = [])
  {
    self::log(__FUNCTION__, $location, $action, $context);
  }
}
