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
    Schema::table('users', function (Blueprint $table) {
      $table->string("cognito_id");
      $table->string("full_name");
      $table->string("country");
      $table->string("language")->default('en');
      $table->unsignedInteger("subscription_level");
      $table->json("roles")->comment('e.g ["admin", "customer", "lds", "siser-backend"]');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn('cognito_id');
    });
  }
};
