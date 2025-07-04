<?php

use App\Models\Plan;
use App\Models\Subscription;
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
    Schema::table('plans', function (Blueprint $table) {
      $table->dropUnique(['name']);

      $table->foreignId('next_plan_id')->nullable()->after('price_list');
      $table->json('next_plan_info')->nullable()->after('next_plan_id');

      $table->foreign('next_plan_id')->references('id')->on('plans');
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
