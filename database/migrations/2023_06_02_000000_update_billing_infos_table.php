<?php

use App\Models\BillingInfo;
use App\Models\Subscription;
use App\Services\Locale;
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
    Schema::table('billing_infos', function (Blueprint $table) {
      $table->string('language')->default('en');
      $table->string('locale')->default('en_US');
    });

    // update billing_infos
    /** @var BillingInfo[] $billing_infos */
    $billing_infos = BillingInfo::all();
    foreach ($billing_infos as $billing_info) {
      $billing_info->language = Locale::defaultLanguage($billing_info->address['country']);
      $billing_info->locale   = Locale::defaultLocale($billing_info->address['country']);
      $billing_info->save();
    }

    // update subscriptions
    /** @var Subscription[] $subscriptions */
    $subscriptions = Subscription::whereNotNull('billing_info')->get();
    foreach ($subscriptions as $subscription) {
      $billing_info = $subscription->billing_info;
      $billing_info['language'] = Locale::defaultLanguage($billing_info['address']['country']);
      $billing_info['locale']   = Locale::defaultLocale($billing_info['address']['country']);
      $subscription->billing_info = $billing_info;
      $subscription->save();
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('billing_infos', function (Blueprint $table) {
      $table->dropColumn('language');
      $table->dropColumn('locale');
    });
  }
};
