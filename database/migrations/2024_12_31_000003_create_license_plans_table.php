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
    Schema::create('license_plans', function (Blueprint $table) {
      $table->id();
      $table->string('product_name');
      $table->foreignId('license_package_id')->constrained()->onDelete('cascade');
      $table->foreignId('plan_id')->unique()->constrained()->onDelete('cascade');
      $table->string('interval')->comments('See Plan::INTERVAL_* constants');
      $table->unsignedInteger('interval_count')->default(1);
      $table->json('details')->nullable()->comment('LicensePlanDetail[]');
      $table->timestamps();

      $table->foreign('product_name')->references('name')->on('products')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
