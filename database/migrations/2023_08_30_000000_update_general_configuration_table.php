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
    // 
    DB::table('general_configuration')->wherein('name', [
      'plan_billing_offset_days',
      'plan_reminder_offset_days',
      'plan_collection_period_days',
    ])->delete();
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
