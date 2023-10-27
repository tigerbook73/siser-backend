<?php

use App\Models\Plan;
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
    Schema::create('subscription_renewals', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users');
      $table->foreignId('subscription_id')->constrained('subscriptions');
      $table->unsignedInteger('period');
      $table->dateTime('start_at')->index();
      $table->dateTime('expire_at')->index();
      $table->dateTime('first_reminder_at')->nullable()->index();
      $table->dateTime('final_reminder_at')->nullable()->index();
      $table->string('status')->comment('See SubscriptionRenewal::STATUS_* constants');
      $table->string('sub_status')->comment('See SubscriptionRenewal::SUB_STATUS_* constants');
      $table->json('status_transitions')->nullable()->comment('{status: timestamps}');
      $table->timestamps();
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
