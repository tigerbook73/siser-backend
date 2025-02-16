<?php

use App\Models\Coupon;
use App\Models\Invoice;
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
    Schema::table('coupons', function (Blueprint $table) {
      $table->unsignedInteger('interval_count')->comment('max recurring period of coupon, 0 for longterm')->change();
      $table->unsignedInteger('interval_size')->default(1)->comment('corresponding field of plan.interval_count ')->after('interval');
    });

    // Update the existing data
    DB::table('coupons')->where('interval', Coupon::INTERVAL_DAY)->update([
      'interval_size' => 2   // 2-day plan, hard-coded
    ]);

    // update coupon_info for subscriptions
    DB::table('subscriptions')
      ->whereNotNull('coupon_info')
      ->chunkById(100, function ($subscriptions) {
        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
          $couponInfo = $subscription->coupon_info;
          $couponInfo['interval_size'] = ($couponInfo['interval'] == Coupon::INTERVAL_DAY) ? 2 : 1;
          $subscription->coupon_info = $couponInfo;
          $subscription->save();
        }
      });

    // update coupon_info for invoices
    DB::table('invoices')
      ->whereNotNull('coupon_info')
      ->chunkById(100, function ($invoices) {
        /** @var Invoice $invoice */
        foreach ($invoices as $invoice) {
          $couponInfo = $invoice->coupon_info;
          $couponInfo['interval_size'] = ($couponInfo['interval'] == Coupon::INTERVAL_DAY) ? 2 : 1;
          $invoice->coupon_info = $couponInfo;
          $invoice->save();
        }
      });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
