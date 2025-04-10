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
      $billingInfo = $billing_info->info();
      $billing_info->language = Locale::defaultLanguage($billingInfo->address->country);
      $billing_info->locale   = Locale::defaultLocale($billingInfo->address->country);
      $billing_info->save();
    }

    // update subscriptions
    /** @var Subscription[] $subscriptions */
    $subscriptions = Subscription::whereNotNull('billing_info')->get();
    foreach ($subscriptions as $subscription) {
      $billingInfo = $subscription->getBillingInfo();
      $billingInfo->language = Locale::defaultLanguage($billingInfo->address->country);
      $billingInfo->locale   = Locale::defaultLocale($billingInfo->address->country);
      $subscription->setBillingInfo($billingInfo);
      $subscription->save();
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
