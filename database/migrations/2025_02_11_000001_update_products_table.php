<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Product::whereNotIn('type', [Product::TYPE_BASIC, Product::TYPE_SUBSCRIPTION])
      ->delete();
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
