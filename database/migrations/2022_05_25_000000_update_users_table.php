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
      $table->string('cognito_id')->nullable()->comment('only for end users');
      $table->string('given_name')->nullable()->comment('only for end users');
      $table->string('family_name')->nullable()->comment('only for end users');
      $table->string('full_name');
      $table->string('phone_number')->nullable()->comment('only for end users');
      $table->string('country_code')->nullable()->comment('only for end users');
      $table->string('language_code')->nullable()->comment('only for end users');
      $table->unsignedInteger('subscription_level')->nullable()->comment('only for end users');
      $table->unsignedInteger('license_count')->nullable()->comment('only for end users');
      $table->json('roles')->nullable()->comment('e.g ["admin", "siser-backend", "support"]')->comment('only for admin users');
      $table->timestamps();
    });

    // admin
    DB::table('users')->insert([
      [
        'id'                  => 1,
        'name'                => 'admin',
        'email'               => 'admin@iifuture.com',
        'full_name'           => 'default admin',
        'roles'               => json_encode(['admin']),
        'password'            => Hash::make('password'),
      ],
      [
        'id'                  => 2,
        'name'                => 'web-team',
        'email'               => 'web-team@siserna.com',
        'full_name'           => 'Web Team',
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
