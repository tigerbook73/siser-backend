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
    Schema::table('products', function (Blueprint $table) {
      $table->string('type')->default(Product::TYPE_SUBSCRIPTION)->comment('Product::TYPE_* constants');
      $table->json('meta')->nullable()->comment('see Product::META_* constants');
      $table->timestamps();
    });

    // insert or update default plans
    DB::table('products')->upsert(
      [
        [
          'id'    => 1,
          'name'  => 'Leonardo® Design Studio Basic',
          'type'  => Product::TYPE_BASIC,
        ],
        [
          'id'    => 2,
          'name'  => 'Leonardo® Design Studio Pro',
          'type'  => Product::TYPE_SUBSCRIPTION,
        ],
        [
          'id'    => 3,
          'name'  => 'License Package',
          'type'  => Product::TYPE_LICENSE_PACKAGE,
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
  public function down() {}
};
