<?php

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
    Schema::table('countries', function (Blueprint $table) {
      $table->renameColumn('country_code', 'code');
      $table->renameColumn('country', 'name');
      $table->string('currency');
      $table->decimal('processing_fee_rate')->default(0.0);
      $table->boolean('explicit_processing_fee')->default(false);
      $table->timestamps();
    });

    // default data
    DB::table('countries')->upsert(
      [
        [
          'code' => 'US',
          'name' => 'The United of America',
          'currency' => 'USD',
          'created_at' => new Carbon(),
          'updated_at' => new Carbon(),
        ],
        [
          'code' => 'AU',
          'name' => 'Australia',
          'currency' => 'AUD',
          'created_at' => new Carbon(),
          'updated_at' => new Carbon(),
        ],
      ],
      ['code']
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
