<?php

use App\Models\Invoice;
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
    Schema::table('invoices', function (Blueprint $table) {
      $table->string('type')->default('')->after('subscription_id');
    });

    DB::table('invoices')->where('period', '<=', 1)->update(['type' => Invoice::TYPE_NEW_SUBSCRIPTION]);
    DB::table('invoices')->where('period', '>', 1)->update(['type' => Invoice::TYPE_RENEW_SUBSCRIPTION]);
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
