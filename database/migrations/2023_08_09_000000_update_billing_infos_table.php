<?php

use App\Models\Invoice;
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
    Schema::table('billing_infos', function (Blueprint $table) {
      $table->string('customer_type')->default('individual')->after('phone')->comments('see BillingInfo::CUSTOMER_TYPE_XXXX');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('billing_infos', function (Blueprint $table) {
      $table->dropColumn('customer_type');
    });
  }
};
