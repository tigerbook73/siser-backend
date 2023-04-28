<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (config('dr.dr_mode') != 'prod') {
      $plan_billing_offset_days = 0;
      $plan_collection_period_days = 0;
      $plan_reminder_offset_days = 1;
    } else {
      $plan_billing_offset_days = 5;
      $plan_collection_period_days = 15;
      $plan_reminder_offset_days = 7;
    }

    // 
    DB::table('general_configuration')->upsert(
      [
        [
          'name' => 'plan_reminder_offset_days',
          'value' => json_encode($plan_reminder_offset_days),
        ],
        [
          'name' => 'plan_billing_offset_days',
          'value' => json_encode($plan_billing_offset_days),
        ],
        [
          'name' => 'plan_collection_period_days',
          'value' => json_encode($plan_collection_period_days),
        ],
        [
          'name' => 'siser_share_rate',
          'value' => json_encode(47.5),
        ],
      ],
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
