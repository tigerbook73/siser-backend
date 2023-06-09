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
      $table->json('payment_method_info')->nullable()->comment(json_encode(json_decode('{
        "type": "creditCard",
        "display_data": {
          "brand": "Visa", 
          "last_four_digits": "1111",
          "expiration_year": 2099, 
          "expiration_month": 12
        },
        "dr": {
          "source_id": "..."
        }
      }')));
    });

    /** @var Invoice[] $invoices */
    $invoices = Invoice::all();
    foreach ($invoices as $invoice) {
      $invoice->payment_method_info = $invoice->user->payment_method->info();
      $invoice->save();
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('invoices', function (Blueprint $table) {
      $table->dropColumn('payment_method_info');
    });
  }
};
