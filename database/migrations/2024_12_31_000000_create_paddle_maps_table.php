<?php

use App\Models\Product;
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
    Schema::create('paddle_maps', function (Blueprint $table) {
      $table->id();
      $table->string('paddle_id')->unique()->comment('Paddle ID');
      $table->unsignedBigInteger('model_id')->comment('id of model in our system');
      $table->string('model_class')->comment('class name of model in our system');
      $table->json('meta')->nullable()->comment('extra data');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
