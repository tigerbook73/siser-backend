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
    Schema::table('license_sharings', function (Blueprint $table) {

      // remove unique index for subscripiton_id
      $table->dropForeign(['subscription_id']);
      $table->dropUnique(['subscription_id']);
      $table->foreign('subscription_id')->references('id')->on('subscriptions');
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
