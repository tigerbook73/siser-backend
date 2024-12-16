<?php

use App\Models\DrEventRecord;
use App\Models\Refund;
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
    Schema::table('refunds', function (Blueprint $table) {
      $table->string('item_type')->default(Refund::ITEM_SUBSCRIPTION)->comment('see Refund::ITEM_TYPE_*')->after('currency');
      $table->json('items')->nullable()->comment('only for Refund::ITEM_TYPE_LICENSE')->after('item_type');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
