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
      $table->string('currency');
      $table->json('plan')->comment('{
        "name": "Premier Plan",
        "price": 10.00
      }');
      $table->json('coupon')->nullable()->comment('{
        "code": "coupon20",
        "percentage_off": 20
      }');
      $table->json('processing_fee')->comment('{
        "processing_fee_rate": 2,
        "explicit_processing_fee": false
      }');
      $table->decimal('subtotal');
      $table->decimal('total_tax');
      $table->decimal('total_amount');
      $table->date('invoice_date');
      $table->string('pdf_file')->nullable();
      $table->json('dr')->comment('{
        "order_id": "dr_order_id",
        "file_id":  "dr_invoice_id",
      }');
      $table->string('status')->comment('[ draft, open, overdue, failed, completed ]');

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
  }
};
