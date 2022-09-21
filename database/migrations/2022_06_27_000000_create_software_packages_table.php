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
    Schema::create('software_packages', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('platform')->comment('Windows|Mac');
      $table->string('version');
      $table->string('description')->nullable();
      $table->string('version_type')->default('stable')->comment('stable|beta|...');
      $table->date('released_date');
      $table->string('release_notes')->nullable()->comment('URL for release notes');
      $table->string('filename');
      $table->string('url')->comment('URL for software package download');
      $table->string('file_hash')->nullable();
      $table->boolean('force_update')->default(false);
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
    Schema::dropIfExists('software_packages');
  }
};
