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
    Schema::create('subscription_plans', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
      $table->string('type')->comment('see SubscriptionPlan::TYPE_* constants');
      $table->string('interval')->defalt(SubscriptionPlan::INTERVAL_MONTH)->comment('see SubscriptionPlan::INTERVAL_* constants');
      $table->unsignedInteger('interval_count')->default(1);
      $table->unsignedInteger('contract_binding_days')->default(365);
      $table->unsignedInteger('billing_offset_days')->default(1);
      $table->unsignedInteger('reminder_offset_days')->default(8);
      $table->unsignedInteger('collection_period_days')->default(10);
      $table->string('status')->comment('see SubscriptionPlan::STATUS_* constants');
      $table->timestamps();
    });

    $billing_offset_days = 1;
    $reminder_offset_days = 8;
    $collection_period_days = 10;
    $subscriptionPlans = [
      [
        // standard-1-month
        'type'                      => SubscriptionPlan::TYPE_STANDARD,
        'interval'                  => SubscriptionPlan::INTERVAL_MONTH,
        'interval_count'            => 1,
        'contract_binding_days'     => 365,
        'billing_offset_days'       => $billing_offset_days,
        'reminder_offset_days'      => $reminder_offset_days,
        'collection_period_days'    => $collection_period_days,
        'status'                    => 'active',
      ],
      [
        // standard-1-year
        'type'                      => SubscriptionPlan::TYPE_STANDARD,
        'interval'                  => SubscriptionPlan::INTERVAL_YEAR,
        'interval_count'            => 1,
        'contract_binding_days'     => 365,
        'billing_offset_days'       => $billing_offset_days,
        'reminder_offset_days'      => $reminder_offset_days,
        'collection_period_days'    => $collection_period_days,
        'status'                    => 'active',
      ],
      [
        // test-2-day
        'type'                      => SubscriptionPlan::TYPE_TEST,
        'interval'                  => SubscriptionPlan::INTERVAL_DAY,
        'interval_count'            => 2,
        'contract_binding_days'     => 365,
        'billing_offset_days'       => 0,
        'reminder_offset_days'      => 1,
        'collection_period_days'    => 1,
        'status'                    => 'active',
      ],
      [
        // test-3-day
        'type'                      => SubscriptionPlan::TYPE_TEST,
        'interval'                  => SubscriptionPlan::INTERVAL_DAY,
        'interval_count'            => 3,
        'contract_binding_days'     => 365,
        'billing_offset_days'       => 1,
        'reminder_offset_days'      => 1,
        'collection_period_days'    => 2,
        'status'                    => 'active',
      ],
      [
        // free-trial-1-month
        'type'                      => SubscriptionPlan::TYPE_FREE_TRIAL,
        'interval'                  => SubscriptionPlan::INTERVAL_MONTH,
        'interval_count'            => 1,
        'contract_binding_days'     => 365,
        'billing_offset_days'       => $billing_offset_days,
        'reminder_offset_days'      => $reminder_offset_days,
        'collection_period_days'    => $collection_period_days,
        'status'                    => 'active',
      ],
      [
        // free-trial-3-month
        'type'                      => SubscriptionPlan::TYPE_FREE_TRIAL,
        'interval'                  => SubscriptionPlan::INTERVAL_MONTH,
        'interval_count'            => 3,
        'contract_binding_days'     => 365,
        'billing_offset_days'       => $billing_offset_days,
        'reminder_offset_days'      => $reminder_offset_days,
        'collection_period_days'    => $collection_period_days,
        'status'                    => 'active',
      ],
      [
        // free-trial-2-day
        'type'                      => SubscriptionPlan::TYPE_FREE_TRIAL,
        'interval'                  => SubscriptionPlan::INTERVAL_DAY,
        'interval_count'            => 2,
        'contract_binding_days'     => 365,
        'billing_offset_days'       => 0,
        'reminder_offset_days'      => 1,
        'collection_period_days'    => 1,
        'status'                    => 'active',
      ],
      [
        // free-trial-3-day
        'type'                      => SubscriptionPlan::TYPE_FREE_TRIAL,
        'interval'                  => SubscriptionPlan::INTERVAL_DAY,
        'interval_count'            => 3,
        'contract_binding_days'     => 365,
        'billing_offset_days'       => 1,
        'reminder_offset_days'      => 1,
        'collection_period_days'    => 2,
        'status'                    => 'active',
      ]
    ];
    for ($i = 0; $i < count($subscriptionPlans); $i++) {
      $subscriptionPlans[$i]['name'] = SubscriptionPlan::buildPlanName($subscriptionPlans[$i]['type'], $subscriptionPlans[$i]['interval'], $subscriptionPlans[$i]['interval_count']);
    }
    DB::table('subscription_plans')->upsert(
      $subscriptionPlans,
      ['name']
    );
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
