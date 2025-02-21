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
    Schema::create('license_packages', function (Blueprint $table) {
      $table->id();
      $table->string('type')->comment('see LicensePackage::TYPE_*');
      $table->string('name');
      $table->json('price_table')->comment('see LicensePackagePriceTable');
      $table->string('status')->comment('see LicensePackage::STATUS_*');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
