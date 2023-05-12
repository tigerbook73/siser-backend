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
    Schema::table('critical_sections', function (Blueprint $table) {
      $table->boolean('need_notify')->default(true);
      $table->index('need_notify');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('critical_sections', function (Blueprint $table) {
      $table->dropIndex(['need_notify']);

      $table->dropColumn('need_notify');
    });
  }
};
