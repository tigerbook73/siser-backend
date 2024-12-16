<?php

use App\Models\Product;
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
    Schema::table('billing_infos', function (Blueprint $table) {
      $table->json('meta')->nullable()->comment('see BillingInfoMeta')->after('locale');
    });

    DB::statement('
      UPDATE billing_infos
      JOIN users ON billing_infos.user_id = users.id
      SET billing_infos.email = users.email
    ');
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
