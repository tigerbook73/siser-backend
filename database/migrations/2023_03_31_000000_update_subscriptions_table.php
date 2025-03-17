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
      $table->json('billing_info')->nullable()->comment('see Billing::info()');
      $table->json('plan_info')->nullable()->comment('see Plan::info()');
      $table->json('coupon_info')->nullable()->comment('see Coupon::info()');
      $table->json('processing_fee_info')->nullable();
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
      $table->json('next_invoice')->nullable()->comment('see SubscriptionNextInvoice::info()');
      $table->json('dr')->nullable();
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
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {}
};
