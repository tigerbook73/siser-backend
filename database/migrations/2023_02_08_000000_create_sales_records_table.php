<?php

use App\Models\Machine;
use App\Models\User;
use Carbon\Carbon;
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
    Schema::create('statistic_records', function (Blueprint $table) {
      $table->id();
      $table->date('date');
      $table->json('record')->comment('{
        "user": 2356,
        "machine": 1550,
        "licensed_user": 1550,
        "licensed_user_1": 1550,
      }');
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
