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
    Schema::create('invoices', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained();
      $table->foreignId('subscription_id')->constrained();
      $table->unsignedInteger('period');
      $table->datetime('period_start_date');
      $table->datetime('period_end_date');
      $table->string('currency');
      $table->json('plan_info')->comment('same as subscription.plan_info');
      $table->json('coupon_info')->nullable()->comment('same as subscription.coupon_info');
      $table->json('processing_fee_info')->comment('same as subscription.processing_fee_info');
      $table->decimal('subtotal');
      $table->decimal('total_tax');
      $table->decimal('total_amount');
      $table->date('invoice_date');
      $table->string('pdf_file')->nullable();
      $table->json('dr')->comment(json_encode(json_decode('{
        "order_id": "dr_order_id",
        "invoice_id": "dr_invoice_id",
        "file_id":  "dr_file_id"
      }')));
      $table->string('dr_invoice_id')->nullable();
      $table->string('dr_order_id')->nullable();
      $table->string('status')->comment('see Invoice::STATUS_*');

      $table->timestamps();

      $table->unique('dr_invoice_id');
      $table->unique('dr_order_id');
    });
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
