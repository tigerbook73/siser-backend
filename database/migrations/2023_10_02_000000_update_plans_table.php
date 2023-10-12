<?php

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    Schema::table('plans', function (Blueprint $table) {
      $table->dropUnique(['name']);

      $table->foreignId('next_plan_id')->nullable()->after('price_list');
      $table->json('next_plan_info')->nullable()->after('next_plan_id');

      $table->foreign('next_plan_id')->references('id')->on('plans');
    });

    // monthly plan
    $monthPlan = Plan::public()
      ->where('interval', Plan::INTERVAL_MONTH)
      ->where('interval_count', 1)
      ->where('subscription_level', 2)
      ->first();

    // update annual plan's next plan
    Plan::public()
      ->where('interval', Plan::INTERVAL_YEAR)
      ->where('interval_count', 1)
      ->where('subscription_level', 2)
      ->update([
        'next_plan_id'    => $monthPlan->id,
        'next_plan_info'  => $monthPlan->buildNextPlanInfo(),
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
