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
    Schema::create('license_sharings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users');
      $table->foreignId('subscription_id')->unique()->constrained('subscriptions');
      $table->string('product_name');
      $table->unsignedInteger('subscription_level');
      $table->unsignedInteger('total_count');
      $table->unsignedInteger('free_count');
      $table->unsignedInteger('used_count');
      $table->string('status')->comment('see LicenseSharing::STATUS_*');
      $table->json('logs')->nullable()->comment('History of operations');
      $table->timestamps();

      $table->foreign('product_name')->references('name')->on('products');
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
