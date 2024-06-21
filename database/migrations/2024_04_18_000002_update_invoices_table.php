<?php

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
    Schema::table('invoices', function (Blueprint $table) {
      $table->json('license_package_info')->nullable()->comment('License package details')->after('coupon_info');
      $table->json('items')->nullable()->comment('invoice items')->after('license_package_info');
    });

    /**
     * update invoice's license_package_info, items
     */
    Invoice::chunkById(200, function ($invoices) {
      /** @var \App\Models\Invoice[] $invoices */
      foreach ($invoices as $invoice) {
        $items = Subscription::buildItems($invoice->plan_info, $invoice->coupon_info);
        $items[0]['price']  = $invoice->subtotal;
        $items[0]['tax']    = $invoice->total_tax;
        $items[0]['amount'] = $invoice->subtotal;
        $invoice->items = $items;
        $invoice->license_package_info = null;
        $invoice->save();
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
