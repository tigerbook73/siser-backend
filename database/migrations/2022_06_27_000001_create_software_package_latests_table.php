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
    Schema::create('software_package_latests', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('platform')->comment('Windows|Mac');
      $table->string('version_type')->default('stable')->comment('stable|beta|...');
      $table->foreignId('software_package_id')->unique()->constrained();
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
    Schema::dropIfExists('software_package_latests');
  }
};
