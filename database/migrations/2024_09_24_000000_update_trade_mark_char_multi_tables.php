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
  }


  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
