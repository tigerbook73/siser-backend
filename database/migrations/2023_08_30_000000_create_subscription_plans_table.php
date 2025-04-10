<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TODO: to be deleted
return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('subscription_plans', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
      $table->string('type')->comment('see SubscriptionPlan::TYPE_* constants');
      $table->string('interval')->comment('see SubscriptionPlan::INTERVAL_* constants');
      $table->unsignedInteger('interval_count')->default(1);
      $table->unsignedInteger('contract_binding_days')->default(365);
      $table->unsignedInteger('billing_offset_days')->default(1);
      $table->unsignedInteger('reminder_offset_days')->default(8);
      $table->unsignedInteger('collection_period_days')->default(10);
      $table->string('status')->comment('see SubscriptionPlan::STATUS_* constants');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
