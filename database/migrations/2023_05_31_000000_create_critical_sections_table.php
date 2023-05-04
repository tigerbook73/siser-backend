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
    Schema::create('critical_sections', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->string('type')->comment('[subscription, user]');
      $table->unsignedBigInteger('object_id');
      $table->json('action')->comment('action object');
      $table->json('steps')->comment('array of steps');
      $table->string('status')->default('open')->comment('[open, closed]');
      $table->timestamps();

      $table->index('type');
      $table->index('object_id');
      $table->index('status');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('critical_sections');
  }
};
