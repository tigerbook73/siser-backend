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
    Schema::create('subscriptions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained();
      $table->foreignId('plan_id')->constrained();
      $table->string('currency');
      $table->float('price');
      $table->date('start_date');
      $table->date('end_date')->nullable();
      $table->string('status')->comment('active|inactive|schedule_start|schedule_terminate');
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
    Schema::dropIfExists('subscriptions');
  }
};
