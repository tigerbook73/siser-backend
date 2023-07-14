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
    Schema::table('countries', function (Blueprint $table) {
      $table->renameColumn('country_code', 'code');
      $table->renameColumn('country', 'name');
      $table->string('currency');
      $table->decimal('processing_fee_rate')->default(0.0);
      $table->boolean('explicit_processing_fee')->default(false);
      $table->timestamps();
    });

    // default data
    $now = now();
    DB::table('countries')->upsert(
      [
        ['code' => 'AE', 'name' => 'United Arab Emirates',      'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'AT', 'name' => 'Austria',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'AU', 'name' => 'Australia',                 'currency' => 'AUD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'AW', 'name' => 'Andorra',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BE', 'name' => 'Belgium',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BN', 'name' => 'Brunei Darussalam',         'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BR', 'name' => 'Brazil',                    'currency' => 'BRL', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BS', 'name' => 'Bahamas',                   'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CA', 'name' => 'Canada',                    'currency' => 'CAD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CH', 'name' => 'Switzerland',               'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CL', 'name' => 'Chile',                     'currency' => 'CLP', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CO', 'name' => 'Columbia',                  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CR', 'name' => 'Costa Rica',                'currency' => 'CRC', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CZ', 'name' => 'Czechia',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'DE', 'name' => 'Germany',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'DK', 'name' => 'Denmark',                   'currency' => 'DKK', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'DO', 'name' => 'Dominican Republic',        'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'EC', 'name' => 'Ecuador',                   'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'EE', 'name' => 'Estonia',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'ES', 'name' => 'Spain',                     'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'FR', 'name' => 'France',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GB', 'name' => 'Great Britain',             'currency' => 'GBR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GF', 'name' => 'French Guiana',             'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GP', 'name' => 'Guadeloupe',                'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GR', 'name' => 'Greece',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GT', 'name' => 'Guatemala',                 'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'HN', 'name' => 'Honduras',                  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'HR', 'name' => 'Croatia',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'ID', 'name' => 'Indonesia',                 'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IE', 'name' => 'Ireland',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IL', 'name' => 'Isreal',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IN', 'name' => 'India',                     'currency' => 'INR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IS', 'name' => 'Iceland',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IT', 'name' => 'Italy',                     'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'JM', 'name' => 'Jamaica',                   'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'JP', 'name' => 'Japan',                     'currency' => 'JPY', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'KR', 'name' => 'Korea',                     'currency' => 'KRW', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'LT', 'name' => 'Lithuania',                 'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'LU', 'name' => 'Luxembourg',                'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'LV', 'name' => 'Latvia',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MN', 'name' => 'Mongolia',                  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MT', 'name' => 'Malta',                     'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MU', 'name' => 'Mauritius',                 'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MX', 'name' => 'Mexico',                    'currency' => 'MXN', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MY', 'name' => 'Malaysia',                  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'NI', 'name' => 'Nicaragua',                 'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'NL', 'name' => 'Netherlands',               'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'NO', 'name' => 'Norway',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'NZ', 'name' => 'New Zealand',               'currency' => 'NZD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PA', 'name' => 'Panama',                    'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PH', 'name' => 'Philippines',               'currency' => 'PHP', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PL', 'name' => 'Poland',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PM', 'name' => 'Saint Pierre and Miquelon', 'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PR', 'name' => 'Puerto Rico',               'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PT', 'name' => 'Portugal',                  'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PY', 'name' => 'Paraguay',                  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'RE', 'name' => 'Reunion',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'RO', 'name' => 'Romania',                   'currency' => 'RON', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'RS', 'name' => 'Serbia',                    'currency' => 'RSD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'SE', 'name' => 'Sweden',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'SG', 'name' => 'Singapore',                 'currency' => 'SGD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'SK', 'name' => 'Slovakia',                  'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'SV', 'name' => 'El Salvador',               'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'TH', 'name' => 'Thailand',                  'currency' => 'THB', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'TR', 'name' => 'Turkiye',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'TT', 'name' => 'Trinidad and Tobago',       'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'TW', 'name' => 'Taiwan',                    'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'UA', 'name' => 'Ukraine',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'US', 'name' => 'United States of America',  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'VE', 'name' => 'Venezuela',                 'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'VI', 'name' => 'Virgin Islands',            'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'ZA', 'name' => 'South Africa',              'currency' => 'ZAR', 'created_at' => $now, 'updated_at' => $now],
      ],
      ['code']
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
