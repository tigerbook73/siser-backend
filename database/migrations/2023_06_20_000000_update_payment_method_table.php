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
      $table->json('display_data')->comment(json_encode(json_decode('{
        "brand": "Visa",
        "last_four_digits": "3119",
        "expiration_year": 2027,
        "expiration_month": 7
      }')))->change();
    });

    /** @var PaymentMethod[] $paymentMethods */
    $paymentMethods = PaymentMethod::all();
    foreach ($paymentMethods as $paymentMethod) {
      $display_data = $paymentMethod->display_data;
      $display_data['expiration_year'] = 2099;
      $display_data['expiration_month'] = 12;
      $paymentMethod->display_data = $display_data;
      $paymentMethod->save();
    }
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
