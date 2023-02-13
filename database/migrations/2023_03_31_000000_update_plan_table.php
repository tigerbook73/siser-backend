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

      $table->json('price_list')->comment('[
        {
          "country": "US",
          "currency": "USD",
          "price": 9.98
        }
      ]');

      $table->unique('name');
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

    DB::table('plans')->insert([
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
