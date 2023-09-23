<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    DB::table('general_configuration')->upsert(
      [
        [
          'name' => 'siser_share_rate',
          'value' => json_encode(47.5),
        ],
      ],
      ['name']
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
