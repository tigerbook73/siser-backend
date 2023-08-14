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
          'url'                 => 'https://www.siserna.com/leonardo-design-studio/',
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
          'name'                => 'Leonardo™ Design Studio Pro Plan',
          'catagory'            => 'machine',
          'description'         => 'Leonardo™ Design Studio Pro Plan',
          'subscription_level'  => 2,
          'price_list'          => json_encode([
            ['country' => 'AE', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'AT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'AU', 'currency' => 'AUD', 'price' => 12.99],
            ['country' => 'AW', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'BE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'BG', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'BN', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'BR', 'currency' => 'BRL', 'price' => 39.99],
            ['country' => 'BS', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'CA', 'currency' => 'CAD', 'price' => 11.49],
            ['country' => 'CH', 'currency' => 'CHF', 'price' => 7.5],
            ['country' => 'CL', 'currency' => 'CLP', 'price' => 6999],
            ['country' => 'CO', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'CR', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'CY', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'CZ', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'DE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'DK', 'currency' => 'DKK', 'price' => 59.99],
            ['country' => 'DO', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'EC', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'EE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'ES', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'FI', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'FR', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'GB', 'currency' => 'GBP', 'price' => 6.99],
            ['country' => 'GF', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'GP', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'GR', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'GT', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'HN', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'HR', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'HU', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'ID', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'IE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'IL', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'IN', 'currency' => 'INR', 'price' => 699],
            ['country' => 'IS', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'IT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'JM', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'JP', 'currency' => 'JPY', 'price' => 1299],
            ['country' => 'KR', 'currency' => 'KRW', 'price' => 11699],
            ['country' => 'LI', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'LT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'LU', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'LV', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'MN', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'MT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'MU', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'MX', 'currency' => 'MXN', 'price' => 149],
            ['country' => 'MY', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'NI', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'NL', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'NO', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'NZ', 'currency' => 'NZD', 'price' => 14.49],
            ['country' => 'PA', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'PH', 'currency' => 'PHP', 'price' => 499],
            ['country' => 'PL', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'PM', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'PR', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'PT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'PY', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'RE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'RO', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'RS', 'currency' => 'ERU', 'price' => 7.99],
            ['country' => 'SE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'SG', 'currency' => 'SGD', 'price' => 11.99],
            ['country' => 'SI', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'SK', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'SV', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'TH', 'currency' => 'THB', 'price' => 299],
            ['country' => 'TR', 'currency' => 'TRY', 'price' => 235],
            ['country' => 'TT', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'TW', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'UA', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'US', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'VE', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'VI', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'ZA', 'currency' => 'ZAR', 'price' => 169.99],
          ]),
          'url'                 => 'https://www.siserna.com/leonardo-design-studio/',
          'status'              => 'active',
          'created_at'          => now(),
          'updated_at'          => now(),
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
  }
};
