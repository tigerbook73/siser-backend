<?php

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\LicensePackage;
use App\Models\LicensePackagePriceTable;
use App\Models\Subscription;
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
