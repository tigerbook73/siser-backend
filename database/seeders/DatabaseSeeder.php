<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Plan;
use App\Models\SoftwarePackage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {

    /**
     * create users
     */
    // end users
    $customer = User::create([
      'name'                => 'john.smith',
      'email'               => 'john.smith@gmail.com',
      'full_name'           => 'John Smith',
      'country'             => 'USA',
      'language'            => 'en',
      'cognito_id'          => '0000-0000-0000-0000',
      'subscription_level'  => 0,
      'roles'               => null,
      'password'            => Hash::make('password'),
    ]);

    /**
     * create software packages
     */
    SoftwarePackage::create([
      'name'            => 'LDS software',
      'platform'        => 'Windows',
      'version'         => '5.0.1',
      'description'     => '',
      'version_type'    => 'stable',
      'released_date'   => now(),
      'release_notes'   => 'https://www.google.com',
      'filename'        => 'lds-software-win-5.0.1.zip',
      // 'is_latest'       => true,
      'url'             => '/favicon.ico',
    ]);
    SoftwarePackage::create([
      'name'            => 'LDS software',
      'platform'        => 'Mac',
      'version'         => '5.0.1',
      'description'     => '',
      'version_type'    => 'stable',
      'released_date'   => now(),
      'release_notes'   => 'https://www.google.com',
      'filename'        => 'lds-software-mac-5.0.1.zip',
      // 'is_latest'       => true,
      'url'             => '/favicon.ico',
    ]);


    Machine::create([
      'serial_no'     => '0000-1111-2222-3333',
      'model'         => 'Siser Cutter XY',
      'manufacture'   => 'Siser',
      'user_id'       => $customer->id,
    ]);
  }
}
