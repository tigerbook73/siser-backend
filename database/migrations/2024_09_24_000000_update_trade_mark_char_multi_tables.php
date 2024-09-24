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
    /**
     * update the trade mark character from ™ -> ®
     */

    /**
     * update schema
     */

    // plans
    Schema::table('plans', function (Blueprint $table) {
      $table->string('product_name')->default('Leonardo® Design Studio Basic')->change();

      $table->dropForeign(['product_name']);
      $table->foreign('product_name')->references('name')->on('products')->onUpdate('cascade');
    });

    // coupons
    Schema::table('coupons', function (Blueprint $table) {
      $table->string('product_name')->default('Leonardo® Design Studio Pro')->change();

      $table->dropForeign(['product_name']);
      $table->foreign('product_name')->references('name')->on('products')->onUpdate('cascade');
    });

    // license_sharing
    Schema::table('license_sharings', function (Blueprint $table) {
      $table->string('product_name')->default('Leonardo® Design Studio Pro')->change();

      $table->dropForeign(['product_name']);
      $table->foreign('product_name')->references('name')->on('products')->onUpdate('cascade');
    });

    // license_sharing_invitations
    Schema::table('license_sharing_invitations', function (Blueprint $table) {
      $table->string('product_name')->default('Leonardo® Design Studio Pro')->change();

      $table->dropForeign(['product_name']);
      $table->foreign('product_name')->references('name')->on('products')->onUpdate('cascade');
    });

    /**
     * products: name
     */
    DB::table('products')->update([
      'name' => DB::raw("REPLACE(name, 'Leonardo™', 'Leonardo®')"),
    ]);

    DB::table('plans')->update([
      'name' => DB::raw("REPLACE(name, 'Leonardo™', 'Leonardo®')"),
      'product_name' => DB::raw("REPLACE(product_name, 'Leonardo™', 'Leonardo®')"),
      'description' => DB::raw("REPLACE(description, 'Leonardo™', 'Leonardo®')"),
    ]);

    /**
     * coupons: product_name
     */
    DB::table('coupons')->update([
      'name' => DB::raw("REPLACE(name, 'Leonardo™', 'Leonardo®')"),
      'product_name' => DB::raw("REPLACE(product_name, 'Leonardo™', 'Leonardo®')"),
    ]);

    /**
     * license_sharing: product_name
     */
    DB::table('license_sharings')->update([
      'product_name' => DB::raw("REPLACE(product_name, 'Leonardo™', 'Leonardo®')"),
    ]);

    /**
     * license_sharing_invitations: product_name
     */
    DB::table('license_sharing_invitations')->update([
      'product_name' => DB::raw("REPLACE(product_name, 'Leonardo™', 'Leonardo®')"),
    ]);

    /**
     * subscriptions: plan_info, items
     */
    DB::table('subscriptions')->update([
      'plan_info' => DB::raw("REPLACE(plan_info, 'Leonardo™', 'Leonardo®')"),
      'coupon_info' => DB::raw("REPLACE(coupon_info, 'Leonardo™', 'Leonardo®')"),
      'items' => DB::raw("REPLACE(items, 'Leonardo™', 'Leonardo®')"),
      'next_invoice' => DB::raw("REPLACE(next_invoice, 'Leonardo™', 'Leonardo®')"),
    ]);

    /**
     * subscriptions: plan_info, items
     */
    DB::table('invoices')->update([
      'plan_info' => DB::raw("REPLACE(plan_info, 'Leonardo™', 'Leonardo®')"),
      'coupon_info' => DB::raw("REPLACE(coupon_info, 'Leonardo™', 'Leonardo®')"),
      'items' => DB::raw("REPLACE(items, 'Leonardo™', 'Leonardo®')"),
    ]);

    /**
     * subscription_logs: data
     */
    DB::table('subscription_logs')->update([
      'data' => DB::raw("REPLACE(data, 'Leonardo™', 'Leonardo®')"),
    ]);

    /**
     * dr_event_raw_records: data
     */
    DB::table('dr_event_raw_records')->update([
      'data' => DB::raw("REPLACE(data, 'Leonardo™', 'Leonardo®')"),
    ]);
  }


  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
