<?php

use App\Models\Plan;
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
    Schema::table('subscriptions', function (Blueprint $table) {
      $table->foreignId('coupon_id')->nullable()->constrained();
      $table->json('billing_info')->nullable()->comment(json_encode(json_decode('{
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
      }')));
      $table->json('plan_info')->nullable()->comment(json_encode(json_decode('{
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
      }')));
      $table->json('coupon_info')->nullable()->comment(json_encode(json_decode('{
        "id": 0,
        "code": "string",
        "description": "string",
        "condition": {
          "new_customer_only": true,
          "new_subscription_only": true,
          "upgrade_only": true
        },
        "percentage_off": 20,
        "period": 6
      }')));
      $table->json('processing_fee_info')->nullable()->comment(json_encode(json_decode('{
        "explicit_processing_fee": true,
        "processing_fee_rate": 2.0
      }')));
      $table->decimal('price')->comment('beautified subscription price, may or may not include processing fee')->change();
      $table->decimal('processing_fee')->default(0.0)->comment('valid when explicit_processing_fee is true');
      $table->decimal('subtotal')->default(0.0)->comment('price + processing_fee');
      $table->decimal('tax_rate')->default(0.0);
      $table->decimal('total_tax')->default(0.0)->comment('based on latest invoice');
      $table->decimal('total_amount')->default(0.0)->comment('subtotal + total_tax');
      $table->unsignedInteger('subscription_level')->default(1);
      $table->unsignedInteger('current_period')->default(0)->comment('0 - not started yet');
      $table->datetime('start_date')->nullable()->change();
      $table->datetime('end_date')->nullable()->change();
      $table->datetime('current_period_start_date')->nullable();
      $table->datetime('current_period_end_date')->nullable();
      $table->datetime('next_invoice_date')->nullable();
      $table->json('next_invoice')->nullable()->comment(json_encode(json_decode('{
        "plan_info": {},
        "coupon_info": {},
        "processing_fee_info": {},
        "price": 10.0,
        "processing_fee": 0.2,
        "subtotal": 10.2,
        "tax_rate": 0.1,
        "total_tax": 1.02,
        "total_amount": 11.22,
        "current_period_start_date": "date-time",
        "current_period_end_date": "date-time"
      }')));
      $table->json('dr')->nullable()->comment(json_encode(json_decode('{
        "checkout_id": "dr_checkout_id",
        "checkout_payment_session_id": "dr_checkout_payment_session_id",
        "order_id": "dr_first_order_id",
        "source_id": "source_id",
        "subscription_id": "dr_subscription_id"
      }')));
      $table->string('dr_subscription_id')->nullable();
      $table->string('stop_reason')->nullable()->comment('[renew-failed, cancelled, new-subscurition]');
      $table->string('status')->comment('[draft, pending, failed, processing, active, stopped]')->change();
      $table->string('sub_status')->comment('[normal, cancelling, invoice-pending]')->default('normal');

      $table->index('subscription_level');
      $table->index('current_period');
      $table->index('dr_subscription_id');
      $table->index('status');
      $table->index('sub_status');
    });

    // change susbscription to basic
    DB::table('subscriptions')
      ->where('plan_id', '<=', config('siser.plan.default_machine_plan'))
      ->update([
        'plan_id' => config('siser.plan.default_machine_plan'),
        'plan_info' => Plan::find(config('siser.plan.default_machine_plan'))->toPublicPlan('US'),
        'subscription_level'        => 1,
        'processing_fee_info'       => [
          'explicit_processing_fee' => false,
          'processing_fee_rate'     => 0,
        ],
        'currency' => 'USD',
        'price' => 0.00,
        'current_period' => 0,
      ]);
    // change 'inactive' => 'stopped'
    DB::table('subscriptions')
      ->where('status', 'inactive')
      ->update([
        'status' => 'stopped',
        'stop_reason' => 'migration',
      ]);
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
