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
    Schema::table('refunds', function (Blueprint $table) {
      // drop unique index on dr_refund_id
      $table->dropUnique(['dr_refund_id']);

      // make dr_refund_id nullable
      $table->string('dr_refund_id')->nullable()->change();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
