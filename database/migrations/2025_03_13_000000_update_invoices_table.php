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
      $table->decimal('credit')->default(0.0)->after('total_amount');
      $table->decimal('grand_total')->default(0.0)->after('credit');
      $table->decimal('credit_to_balance')->default(0.0)->after('grand_total');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
