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
    Schema::create('lds_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('lds_instance_id');
      $table->string('action')->comment('check-in|check-out');
      $table->string('result')->comment('ok|nok');
      $table->string('text')->nullable();
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
    Schema::dropIfExists('lds_instances');
  }
};
