<?php

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
    Schema::table('subscriptions', function (Blueprint $table) {
      $table->json('payment_method_info')->nullable()->after('coupon_info')->comment('see PaymentMethod::info()');
      $table->datetime('next_reminder_date')->nullable()->after('next_invoice_date');
    });

    Subscription::where('subscription_level', '>', 1)
      ->where('status', Subscription::STATUS_ACTIVE)
      ->chunkById(200, function ($subscriptions) {
        /** @var Subscription[] $subscriptions */
        foreach ($subscriptions as $subscription) {
          $subscription->payment_method_info = $subscription->user->payment_method?->info();
          $subscription->next_reminder_date = $subscription->next_invoice_date->subDays(7);
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
