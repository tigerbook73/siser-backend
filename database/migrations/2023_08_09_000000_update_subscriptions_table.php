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
    Schema::table('subscriptions', function (Blueprint $table) {
      $table->json('tax_id_info')->nullable()->after('billing_info')->comment('see TaxId::info()');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('subscriptions', function (Blueprint $table) {
      $table->dropColumn('tax_id_info');
    });
  }
};
