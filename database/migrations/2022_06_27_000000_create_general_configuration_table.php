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
      $table->string('name')->unique();
      $table->json('value');
      $table->timestamps();
    });

    // 
    DB::table('general_configuration')->insert([
      [
        'name'  => 'machine_license_unit',
        'value' => json_encode(2),
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
