<?php

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
    Schema::table('subscriptions', function (Blueprint $table) {
      $table->json('license_package_info')->nullable()->after('coupon_info');
      $table->json('items')->nullable()->after('license_package_info');
    });

    /**
     * update subscription's license_package_info, items, next_invoice['license_package_info'], next_invoice['items']
     */
    Subscription::chunkById(200, function ($subscriptions) {
      /** @var \App\Models\Subscription[] $subscriptions */
      foreach ($subscriptions as $subscription) {
        // current
        $subscription->price = $subscription->subtotal;
        $subscription->license_package_info = null;
        $items = Subscription::buildItems($subscription->plan_info, $subscription->coupon_info);
        $items[0]['price']  = $subscription->subtotal;
        $items[0]['tax']    = $subscription->total_tax;
        $items[0]['amount'] = $subscription->subtotal;
        $subscription->items = $items;

        // next
        if ($subscription->next_invoice != null) {
          $next_invoice = $subscription->next_invoice;
          $next_invoice['license_package_info'] = null;
          $items = Subscription::buildItems($next_invoice['plan_info'], $next_invoice['coupon_info']);
          $items[0]['price']  = $next_invoice['subtotal'];
          $items[0]['tax']    = $next_invoice['total_tax'];
          $items[0]['amount'] = $next_invoice['subtotal'];
          $next_invoice['items'] = $items;
          $subscription->next_invoice = $next_invoice;
        }
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
