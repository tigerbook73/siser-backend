<?php

use App\Models\Plan;
use App\Models\Subscription;
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
    Schema::table('plans', function (Blueprint $table) {
      $table->dropColumn('catagory');

      $table->string('product_name')->default('Leonardo™ Design Studio Basic')->comment('See table "products"')->after('name');
      $table->string('interval')->default(Plan::INTERVAL_MONTH)->comments('see Plan::INTERVAL_* constants')->after('product_name');
      $table->unsignedInteger('interval_count')->default(1)->after('interval');

      $table->foreign('product_name')->references('name')->on('products');
    });

    // Update default machine plan
    Plan::where('subscription_level', '>', 1)
      ->update([
        'product_name'  => 'Leonardo™ Design Studio Pro',
      ]);

    Subscription::where('status', Subscription::STATUS_ACTIVE)
      ->where('subscription_level', '>', 1)
      ->chunk(200, function ($subscriptions) {
        /** @var Subscription[] $subscriptions */
        foreach ($subscriptions as $subscription) {
          $subscription->plan_info = $subscription->plan->info($subscription->billing_info['address']['country']);
          $subscription->save();
        }
      });
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
