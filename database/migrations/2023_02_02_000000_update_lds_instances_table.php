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
    Schema::table('lds_instances', function (Blueprint $table) {
      $table->foreign('lds_registration_id')->references('id')->on('lds_registrations');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('lds_instances', function (Blueprint $table) {
      $table->dropForeign(['lds_registration_id']);
    });
  }
};
