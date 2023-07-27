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
      $table->datetime('period_start_date')->nullable()->change();
      $table->datetime('period_end_date')->nullable()->change();
      $table->decimal('subtotal')->default(0.0)->change();
      $table->decimal('total_tax')->default(0.0)->change();
      $table->decimal('total_amount')->default(0.0)->change();
      $table->dateTime('invoice_date')->nullable()->change();
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
