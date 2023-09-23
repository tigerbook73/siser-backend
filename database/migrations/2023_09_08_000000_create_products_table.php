<?php

use App\Models\Plan;
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
    Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
    });

    // insert default plans
    DB::table('products')->upsert(
      [
        [
          'id' => 1,
          'name' => 'Leonardo™ Design Studio Basic'
        ],
        [
          'id' => 2,
          'name' => 'Leonardo™ Design Studio Pro'
        ],
      ],
      'id',
    );
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
  }
};
