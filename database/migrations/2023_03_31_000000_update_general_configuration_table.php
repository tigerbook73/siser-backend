<?php

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
    // 
    DB::table('general_configuration')->insert([
      [
        'name' => 'plan_reminder_offset_days',
        'value' => json_encode(7),
      ],
      [
        'name' => 'plan_billing_offset_days',
        'value' => json_encode(5),
      ],
      [
        'name' => 'plan_collection_period_days',
        'value' => json_encode(15),
      ],
      [
        'name' => 'siser_share_rate',
        'value' => json_encode(47.5),
      ],
    ]);
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
