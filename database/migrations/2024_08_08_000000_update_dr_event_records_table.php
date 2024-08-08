<?php

use App\Models\DrEventRecord;
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
    Schema::table('dr_event_records', function (Blueprint $table) {
      $table->string('resolve_status')->default(DrEventRecord::RESOLVE_STATUS_UNRESOLVED)->comment('')->after('status_transitions');
      $table->string('resolve_comments')->default('')->after('resolve_status');
    });

    DB::table('dr_event_records')
      ->whereIn('status', [DrEventRecord::STATUS_COMPLETED])
      ->update(
        ['resolve_status' => DrEventRecord::RESOLVE_STATUS_RESOLVED]
      );
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
