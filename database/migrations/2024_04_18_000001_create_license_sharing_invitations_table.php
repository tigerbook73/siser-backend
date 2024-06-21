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
    Schema::create('license_sharing_invitations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('license_sharing_id')->constrained('license_sharings');
      $table->string('product_name')->default('Leonardo™ Design Studio Pro');
      $table->unsignedInteger('subscription_level');
      $table->foreignId('owner_id')->constrained('users');
      $table->string('owner_name');
      $table->string('owner_email');
      $table->foreignId('guest_id')->constrained('users');
      $table->string('guest_name');
      $table->string('guest_email');
      $table->dateTime('expires_at');
      $table->string('status')->comment('see LicenseSharingInvitation::STATUS_*');
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
