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
    Schema::create('dr_event_records', function (Blueprint $table) {
      $table->id();
      $table->string('event_id')->unique();
      $table->string('type');
      $table->foreignId('subscription_id')->nullable();
      $table->string('status')->default(DrEventRecord::STATUS_COMPLETED)->coment('See DrEventRecord::STATUS_* constants');
      $table->json('status_transitions')->nullable()->comment('{ "status": "timestamps" }');
      $table->timestamps();
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
