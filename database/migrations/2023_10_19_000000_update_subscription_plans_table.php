<?php

use App\Models\SubscriptionPlan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    // annual plan
    $yearBillingOffsetDays = 3;
    $yearReminderOffsetDays = 30;
    $yearCollectionPeriodDays = 10;

    /** @var SubscriptionPlan $subscriptionPlan */
    foreach (SubscriptionPlan::where('interval', SubscriptionPlan::INTERVAL_YEAR)
      ->where('interval_count', 1)
      ->where('status', SubscriptionPlan::STATUS_ACTIVE)
      ->get() as $subscriptionPlan) {
      $subscriptionPlan->billing_offset_days = $yearBillingOffsetDays;
      $subscriptionPlan->reminder_offset_days = $yearReminderOffsetDays;
      $subscriptionPlan->collection_period_days = $yearCollectionPeriodDays;
      $subscriptionPlan->save();
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
  }
};
