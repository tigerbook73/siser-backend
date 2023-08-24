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
    Schema::create('refunds', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained();
      $table->foreignId('subscription_id')->constrained();
      $table->foreignId('invoice_id')->constrained();
      $table->string('currency');
      $table->decimal('amount');
      $table->string('reason')->nullable();
      $table->json('payment_method_info')->nullable()->comment('See PaymentMethod::info()');
      $table->json('dr')->nullable()->comment(json_encode(json_decode('{
        "refund_id":  "dr_refund_id"
      }')));
      $table->string('dr_refund_id')->unique();
      $table->string('status')->default('pending')->comments('pending, failed, completed');
      $table->json('status_transitions')->nullable()->comment('{ "status": "timestamps" }');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('refunds');
  }
};
