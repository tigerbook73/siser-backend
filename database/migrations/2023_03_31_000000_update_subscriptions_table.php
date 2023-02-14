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
    Schema::table('subscriptions', function (Blueprint $table) {
      $table->foreignId('coupon_id')->nullable()->constrained();
      $table->json('billing_info')->nullable()->comment('{
        "first_name": "string",
        "last_name": "string",
        "phone": "string",
        "organization": "string",
        "email": "user@example.com",
        "address": {
          "line1": "10380 Bren Rd W",
          "line2": "string",
          "city": "Minnetonka",
          "postcode": "55129",
          "state": "MN",
          "country": "US"
        },
        "tax_id": {
          "type": "string",
          "value": "string"
        }
      }');
      $table->json('plan_info')->nullable()->comment('{
        "id": 0,
        "name": "LDS Machine Basic",
        "catagory": "machine",
        "description": "string",
        "subscription_level": 1,
        "url": "string",
        "status": "draft",
        "price": {
          "country": "US",
          "currency": "USD",
          "price": 9.98
        }
      }');
      $table->json('coupon_info')->nullable()->comment('{
        "id": 0,
        "code": "string",
        "description": "string",
        "condition": {
          "new_customer_only": true,
          "new_subscription_only": true,
          "upgrade_only": true
        },
        "percentage_off": 20,
        "period": 6,
      }');
      $table->json('processing_fee_info')->nullable()->comment('{
        "explicit_processing_fee": true,
        "processing_fee_rate": 2.0,
      }');
      $table->double('price')->comment('beautified subscription price, may or may not include processing fee')->change();
      $table->double('processing_fee')->default(0.0)->comment('valid when explicit_processing_fee is true');
      $table->double('tax')->default(0.0)->comment('based on latest invoice');
      $table->unsignedInteger('subscription_level')->default(0);
      $table->unsignedInteger('current_period')->default(0)->comment('0 - not started yet');
      $table->date('start_date')->nullable()->change();
      $table->date('current_period_start_date')->nullable();
      $table->date('current_period_end_date')->nullable();
      $table->date('next_invoice_date')->nullable();

      $table->string('status')->comment('[draft, pending-payment, processing, active, failed, cancelled]')->change();
      $table->string('sub_status')->comment('[normal, cancelling, overdue]')->default('normal');

      $table->index('subscription_level');
      $table->index('current_period');
      $table->index('status');
      $table->index('sub_status');
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
