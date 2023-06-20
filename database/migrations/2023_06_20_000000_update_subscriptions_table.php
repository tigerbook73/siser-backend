<?php

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
      $table->dropColumn('processing_fee_info');
      $table->dropColumn('processing_fee');
      $table->json('next_invoice')->nullable()->comment(json_encode(json_decode('{
        "plan_info": {},
        "coupon_info": {},
        "price": 10.0,
        "subtotal": 10.2,
        "tax_rate": 0.1,
        "total_tax": 1.02,
        "total_amount": 11.22,
        "current_period_start_date": "date-time",
        "current_period_end_date": "date-time"
      }')))->change();
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
