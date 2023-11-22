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
    Schema::create('subscription_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->index();
      $table->string('event')->index();
      $table->date('date')->index();
      $table->dateTime('date_time')->index();
      $table->json('data')->comment('See SubscriptionLog');
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
