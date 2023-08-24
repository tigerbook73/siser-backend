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
    Schema::table('invoices', function (Blueprint $table) {
      $table->decimal('total_refunded')->default(0)->after('total_amount');
      $table->json('billing_info')->nullable()->after('currency')->comment('see BillingInfo::info()');
      $table->json('tax_id_info')->nullable()->after('billing_info')->comment('see TaxId::info()');
      $table->json('credit_memos')->nullable()->after('pdf_file')->comment('see Invoice::addCreditMemo()');
    });
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
