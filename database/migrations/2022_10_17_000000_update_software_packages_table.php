<?php

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
    Schema::table('software_packages', function (Blueprint $table) {
      // add status column
      $table->string('status')->nullable()->default('active')->comment('active, inactive');
    });

    // udpate existing records
    DB::table('software_packages')->update([
      'status' => 'active',
    ]);
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
