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
    Schema::create('billing_infos', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->unique()->constrained();
      $table->string('first_name');
      $table->string('last_name');
      $table->string('phone')->nullable();
      $table->string('organization')->nullable();
      $table->string('email');
      $table->json('address')->comment(json_encode(json_decode('{
        "line1": "10380 Bren Rd W",
        "line2": "string",
        "city": "Minnetonka",
        "postcode": "55129",
        "state": "MN",
        "country": "US"
      }')));
      $table->json('tax_id')->nullable()->comment(json_encode(json_decode('{
        "type": "string",
        "value": "string"
      }')));

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
