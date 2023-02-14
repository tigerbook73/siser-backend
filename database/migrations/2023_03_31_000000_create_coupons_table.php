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
    Schema::create('coupons', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->string('description')->nullable();
      $table->double('percentage_off');
      $table->unsignedInteger('period')->comment('0 means forever');
      $table->json('condition')->comment('{
        "new_customer_only": true,
        "new_subscription_only": true,
        "upgrade_only": true
      }');
      $table->date('start_date');
      $table->date('end_date')->default('2099-12-31');
      $table->string('status')->comment('[draft, active, inactive]');
      $table->timestamps();

      $table->index('status');
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
