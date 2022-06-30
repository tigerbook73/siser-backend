<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
      $table->dropTimestamps(); // make sure timestamps are last columns
    });

    Schema::table('users', function (Blueprint $table) {
      $table->string("cognito_id")->nullable();
      $table->string("full_name");
      $table->string("country")->nullable();
      $table->string("language")->nullable();
      $table->unsignedInteger("subscription_level")->nullable();
      $table->json("roles")->nullable()->comment('e.g ["admin", "lds", "siser-backend"]');
      $table->timestamps();
    });

    // admin
    DB::table('users')->insert([
      [
        'id'                  => 1,
        'name'                => 'admin',
        'email'               => 'admin@iifuture.com',
        'full_name'           => 'admin',
        'country'             => 'USA',
        'language'            => 'en',
        'cognito_id'          => null,
        'subscription_level'  => 0,
        'roles'               => json_encode(['admin']),
        'password'            => Hash::make('password'),
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
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn('cognito_id');
    });
  }
};
