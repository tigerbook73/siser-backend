<?php

use Carbon\Carbon;
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
    Schema::table('plans', function (Blueprint $table) {
      $table->dropColumn('contract_term');
      $table->dropColumn('price');
      $table->dropColumn('auto_renew');

      $table->json('price_list')->comment(json_encode(json_decode('[
        {
          "country": "US",
          "currency": "USD",
          "price": 9.98
        }
      ]')));

      $table->unique('name');
      $table->index('catagory');
      $table->index('status');
    });

    DB::table('plans')->upsert(
      [
        [
          'id'                  => config('siser.plan.default_machine_plan'),
          'name'                => 'Machine Basic Plan (free)',
          'catagory'            => 'machine',
          'description'         => 'Machine Basic Plan (free)',
          'subscription_level'  => 1,
          'price_list'          => json_encode([
            [
              'country'         => 'US',
              'currency'        => 'USD',
              'price'           => 0.0
            ]
          ]),
          'url'                 => '',
          'status'              => 'active',
          'created_at'          => new Carbon(),
          'updated_at'          => new Carbon(),
        ]
      ],
      ['id']
    );

    DB::table('plans')->upsert(
      [
        [
          'name'                => 'LDS Premier Plan',
          'catagory'            => 'machine',
          'description'         => 'LDS Premier Plan',
          'subscription_level'  => 2,
          'price_list'          => json_encode([
            [
              'country'         => 'US',
              'currency'        => 'USD',
              'price'           => 10.0
            ],
            [
              'country'         => 'AU',
              'currency'        => 'AUD',
              'price'           => 14.0
            ]
          ]),
          'url'                 => '',
          'status'              => 'active',
          'created_at'          => new Carbon(),
          'updated_at'          => new Carbon(),
        ]
      ],
      ['name']
    );
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('plans', function (Blueprint $table) {
      $table->dropUnique(['name']);
      $table->dropIndex(['catagory']);
      $table->dropIndex(['status']);

      $table->dropColumn('price_list');

      $table->string('contract_term')->nullable();
      $table->json('price')->nullable();
      $table->boolean('auto_renew')->default(true);
    });
  }
};
