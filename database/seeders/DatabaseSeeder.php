<?php

namespace Database\Seeders;

use App\Models\Base\BillingInfo;
use App\Models\Coupon;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\SoftwarePackage;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {
    $this->createTestData();
  }

  public function createTestData()
  {
    /**
     * create users
     */
    // $customer = User::createOrUpdateFromCognitoUser((new CognitoProvider)->getUserByName('user1.test'));
    $customer = User::create([
      'id'            =>  3,
      'name'          =>  "user1.test",
      'cognito_id'    =>  "b0620f5c-cada-4f75-a8b1-811ea8ddf69d",
      'email'         =>  "user1.test@iifuture.com",
      'given_name'    =>  "User1",
      'family_name'   =>  "Test",
      'full_name'     =>  "User1 Test",
      'phone_number'  =>  "+61400000000",
      'country_code'  =>  "AU",
      'language_code' =>  "en",
      'password'      => 'not allowed',
      'timezone'      => 'Australia/Sydney',
    ]);

    // additional customer
    User::create([
      'id'            =>  28,
      'name'          =>  "user2.test",
      'cognito_id'    =>  "2ace5639-feb3-49b8-a718-ca7f5644d171",
      'email'         =>  "user2.test@iifuture.com",
      'given_name'    =>  "User2",
      'family_name'   =>  "Test",
      'full_name'     =>  "User2 Test",
      'phone_number'  =>  "+61000000000",
      'country_code'  =>  "AU",
      'language_code' =>  "en",
      'password'      => 'not allowed',
      'timezone'      => 'Australia/Sydney',
    ]);

    /**
     * create software packages
     */
    SoftwarePackage::create([
      'name'                => 'LDS software',
      'platform'            => 'Windows',
      'version'             => '0.0.1',
      'description'         => '__test__',
      'version_type'        => 'stable',
      'released_date'       => now(),
      'release_notes'       => 'https://www.google.com',
      'release_notes_text'  => ['lines' => ['test data']],
      'filename'            => 'lds-software-win-0.0.1.zip',
      // 'is_latest'           => true,
      'url'                 => '/favicon.ico',
    ]);

    SoftwarePackage::create([
      'name'                => 'LDS software',
      'platform'            => 'Mac',
      'version'             => '0.0.1',
      'description'         => '__test__',
      'version_type'        => 'stable',
      'released_date'       => now(),
      'release_notes'       => 'https://www.google.com',
      'release_notes_text'  => ['lines' => ['test data']],
      'filename'            => 'lds-software-mac-0.0.1.zip',
      // 'is_latest'          => true,
      'url'                 => '/favicon.ico',
    ]);

    Coupon::create([
      'code' => 'seeder23',
      'description' => '23% discount in 3 month --test--',
      'percentage_off' => 23,
      'period' => 3,
      'condition' => [
        "new_customer_only" => false,
        "new_subscription_only" => false,
        "upgrade_only" => false,
      ],
      'start_date' => '2023-01-01',
      'end_date' => '2099-12-31',
      'status' => 'active',
    ]);

    Machine::create([
      'serial_no'     => '0000-1111-2222-3333',
      'model'         => 'Siser Cutter XY',
      'nickname'      => '__test__',
      'user_id'       => $customer->id,
    ]);
  }
}
