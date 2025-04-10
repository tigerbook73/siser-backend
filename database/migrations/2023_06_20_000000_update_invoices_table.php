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
    Schema::table('invoices', function (Blueprint $table) {
      $table->dropColumn('processing_fee_info');

      $table->dateTime('invoice_date')->change();
      $table->json('status_transitions')->nullable()->comment('{ "status": "timestamps" }');
      $table->json('payment_method_info')->nullable()->comment('see PaymentMethod::info()');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
