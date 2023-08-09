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
    Schema::create('tax_ids', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained();
      $table->string('dr_tax_id')->unique();
      $table->string('country');
      $table->string('customer_type')->comment('see: TaxId::CUSTOMER_TYPE_XXXX');
      $table->string('type');
      $table->string('value');
      $table->string('status')->comment('see: TaxId::STATUS_XXXX');
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
    Schema::drop('tax_ids');
  }
};
