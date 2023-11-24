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
    Schema::create('coupon_events', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
    });

    $couponEvents = DB::table('coupons')->select('coupon_event')->distinct()->get();
    foreach ($couponEvents as $couponEvent) {
      DB::table('coupon_events')
        ->upsert(
          ['name' => $couponEvent->coupon_event ?? ""],
          ['name']
        );
    }
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
