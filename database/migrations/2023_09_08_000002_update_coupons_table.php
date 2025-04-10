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
    Schema::table('coupons', function (Blueprint $table) {
      $table->dropColumn('description');
      $table->renameColumn('period', 'interval_count');

      $table->string('name')->after('code');
      $table->string('product_name')->default("Leonardo® Design Studio Pro")->comment('See table "products"')->after('name');
      $table->string('type')->comment('see Coupon::TYPE_* constants')->after('description');
      $table->string('coupon_event')->nullable()->after('type');
      $table->string('discount_type')->comment('see Coupon::TYPE_* constants')->after('coupon_event');
      $table->string('interval')->comments('see Coupon::INTERVAL_* constants')->after('percentage_off');
      $table->json('usage')->nullable()->comments('See Coupon::setUsage()')->after('status');

      $table->foreign('product_name')->references('name')->on('products');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
