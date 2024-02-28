<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * design of sales_transactions
 * 
 * create records
 * 1. fetch sales_transactions from digital river via API calls (from last date to yesterday)
 * 2. run fetching operation daily
 * 
 * update records
 * 1. when a summary is created, fetch all transactions for that summary and update all transactions with the summary id
 * 
 */


return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('sdr_configuration', function (Blueprint $table) {
      $table->string('name')->primary();
      $table->string('value')->nullable();
    });
    DB::table('sdr_configuration')->upsert([
      ['name' => 'payouts.latest_id', 'value' => null],
      ['name' => 'sales_summaries.latest_id', 'value' => null],
      ['name' => 'sales_transactions.latest_id', 'value' => null],
    ], 'name');

    Schema::create('sdr_payouts', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->dateTime('payoutTime');
      $table->string('currency');
      $table->float('amount');
      $table->json('data');
    });

    Schema::create('sdr_sales_summaries', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->string('payoutId')->nullable();
      $table->dateTime('salesClosingTime');
      $table->string('currency');
      $table->float('amount');
      $table->boolean('paid');
      $table->json('data');

      $table->foreign('payoutId')->references('id')->on('sdr_payouts');
    });

    Schema::create('sdr_sales_transactions', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->string('salesSummaryId')->nullable();
      $table->date('saleTime');
      $table->string('currency');
      $table->float('amount');
      $table->string('type');
      $table->string('orderId');
      $table->string('orderUpstreamId')->nullable();
      $table->string('billToCountry');
      $table->string('payoutAmounts_currency');
      $table->float('payoutAmounts_amount');
      $table->float('payoutAmounts_tax');
      $table->float('payoutAmounts_productPrice');
      $table->float('payoutAmounts_digitalRiverShare');
      $table->float('payoutAmounts_transactionFees');
      $table->float('payoutAmounts_payoutAmount');
      $table->json('data');

      $table->foreign('salesSummaryId')->references('id')->on('sdr_sales_summaries');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('sdr_configuration');
    Schema::dropIfExists('sdr_sales_transactions');
    Schema::dropIfExists('sdr_sales_summaries');
    Schema::dropIfExists('sdr_payouts');
  }
};
