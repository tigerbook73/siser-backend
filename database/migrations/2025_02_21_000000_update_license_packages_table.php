<?php

use App\Models\LicensePackage;
use App\Models\LicensePackagePriceTable;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    foreach (LicensePackage::all() as $licensePackage) {
      $licensePackage->price_table = LicensePackagePriceTable::from($licensePackage->price_table);
      $licensePackage->save();
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
