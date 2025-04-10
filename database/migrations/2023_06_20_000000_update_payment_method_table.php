<?php

use App\Models\PaymentMethod;
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
    Schema::table('payment_methods', function (Blueprint $table) {
      $table->json('display_data')->comment('see PaymentMethodDisplayData')->change();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
