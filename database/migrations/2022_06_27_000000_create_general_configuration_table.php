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
    Schema::create('general_configuration', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('machine_license_unit');
      $table->timestamps();
    });

    // only one record is allowed
    DB::table('general_configuration')->insert([
      [
        'id'                    => 1,
        'machine_license_unit'  => 2,
      ]
    ]);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('general_configuration');
  }
};
