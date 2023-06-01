<?php

use App\Models\Country;
use App\Services\TimeZone;
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
      $table->string('timezone')->default('UTC');
    });

    // update countries
    /** @var Country[] $countries */
    $countries = Country::all();
    foreach ($countries as $country) {
      $country->timezone = TimeZone::default($country->code);
      $country->save();
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('countries', function (Blueprint $table) {
      $table->dropColumn('timezone');
    });
  }
};
