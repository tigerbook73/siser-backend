<?php

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
    Schema::create('plans', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('catagory')->comment('machine|software');
      $table->string('description');
      $table->unsignedInteger('subscription_level')->comment('0|1|2|3, 0 - none, 1 - basic, 2 - pro, 3 - pro+');
      $table->string('contract_term')->comment('month');
      $table->json('price')->comment('{ "currenty": USD, "price": "19.00" }');
      $table->boolean('auto_renew')->default(true);
      $table->string('url')->nullable()->comment('introduction URL of the plan');
      $table->string('status')->comment('active|inactive');
      $table->timestamps();
    });

    DB::table('plans')->insert([
      [
        'id'                  => config('siser.plan.default_machine_plan'),
        'name'                => 'Leonardo™ Design Studio Basic Plan (free)',
        'catagory'            => 'machine',
        'description'         => 'Leonardo™ Design Studio Basic Plan (free)',
        'subscription_level'  => 1,
        'contract_term'       => 'permanent',
        'price'               => json_encode([
          ['currency' => 'USD', 'price' => '0.0']
        ]),
        'auto_renew'          => true,
        'url'                 => '',
        'status'              => 'active',
      ]
    ]);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('plans');
  }
};
