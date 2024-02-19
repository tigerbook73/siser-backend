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
      $table->string('dispute_status')->default(Invoice::DISPUTE_STATUS_NONE)->comment('see Invoice::DISPUTE_STATUS_*')->after('sub_status');
      $table->json('dispute_status_transitions')->nullable()->comment('{ "status": "timestamps" }')->after('status_transitions');

      $table->index('dispute_status');
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
