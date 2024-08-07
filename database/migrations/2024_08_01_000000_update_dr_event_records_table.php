<?php

use App\Models\DrEventRecord;
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
    Schema::table('dr_event_records', function (Blueprint $table) {
      $table->foreignId('user_id')->nullable()->after('type');
      $table->json('data')->nullable()->comment('{ "subscription_id: xxx", "invoice_id": xxx, ... }')->after('subscription_id');
      $table->json('messages')->nullable()->after('data');
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
