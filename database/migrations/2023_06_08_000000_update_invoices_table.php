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
      $table->json('status_transitions')->nullable()->comment('{ "status": "timestamps" }');
    });

    /** @var Invoice[] $invoice */
    $invoices = Invoice::all();
    foreach ($invoices as $invoice) {
      $status_transitions[Invoice::STATUS_OPEN] = $invoice->created_at;
      if (
        $invoice->status == Invoice::STATUS_COMPLETED ||
        $invoice->status == Invoice::STATUS_FAILED
      ) {
        $status_transitions[$invoice->status] = $invoice->updated_at;
      }
      $invoice->status_transitions = $status_transitions;
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
      $table->dropColumn('status_transitions');
    });
  }
};
