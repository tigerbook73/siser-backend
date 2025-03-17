<?php

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
    Schema::create('payment_methods', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->unique()->constrained();
      $table->string('type')->comment('[creditCard, PayPalBilling]');
      $table->json('display_data')->nullable()->comment('see PaymentMethodDisplayData');
      $table->json('dr')->comment(json_encode(json_decode('{
        "source_id": "dr_source_id"
      }')));
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
