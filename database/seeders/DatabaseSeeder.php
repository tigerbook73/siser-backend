<?php

namespace Database\Seeders;

use App\Models\BillingInfo;
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
    /** @var User $customer */
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
      // 'type'          => User::TYPE_NORMAL,
    ]);

    $billingInfo = $customer->billing_info ?? BillingInfo::createDefault($customer);
    $billingInfo->fill([
      "address" => [
        "line1" => "101 Collins Street",
        "line2" => "",
        "city" => "Melbourne",
        "postcode" => "3000",
        "state" => "VIC",
        "country" => "AU"
      ],
      "language" => "en",
      "locale" => "en_US"
    ]);
    $billingInfo->save();

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

    // create annual plan
    /** @var Plan $monthPlan */
    $monthPlan = Plan::public()->where('interval', 'month')->where('interval_count', 1)->first();
    $annualPlanPriceList = $monthPlan->price_list;
    for ($index = 0; $index < count($annualPlanPriceList); $index++) {
      $annualPlanPriceList[$index]['price'] = round($annualPlanPriceList[$index]['price'] * 12 * 0.9);
    }
    $annualPlanData = [
      'name' => 'Leonardo™ Design Studio Pro Anual Plan',
      'product_name' => $monthPlan->product_name,
      'interval' => Plan::INTERVAL_YEAR,
      'interval_count' => 1,
      'description' => 'annual plan',
      'subscription_level' => $monthPlan->subscription_level,
      'url' => $monthPlan->url,
      'price_list' => $annualPlanPriceList,
      'status' => 'active',
    ];
    Plan::create($annualPlanData);

    Plan::create([
      'name' => 'LDS Test 2-day Plan',
      'product_name' => 'Leonardo™ Design Studio Pro',
      'interval' => Plan::INTERVAL_DAY,
      'interval_count' => 2,
      'description' => '2-day plan',
      'subscription_level' => 2,
      'url' => 'https://www.siserna.com/leonardo-design-studio/',
      'price_list' => [
        [
          'country' => 'US',
          'currency' => 'USD',
          'price' => 20.00,
        ],
        [
          'country' => 'AU',
          'currency' => 'AUD',
          'price' => 22.00,
        ],
      ],
      'status' => 'active',
    ]);

    Plan::create([
      'name' => 'LDS Test 3-day Plan',
      'product_name' => 'Leonardo™ Design Studio Pro',
      'interval' => Plan::INTERVAL_DAY,
      'interval_count' => 3,
      'description' => '3-day plan',
      'subscription_level' => 2,
      'url' => 'https://www.siserna.com/leonardo-design-studio/',
      'price_list' => [
        [
          'country' => 'US',
          'currency' => 'USD',
          'price' => 30.00,
        ],
        [
          'country' => 'AU',
          'currency' => 'AUD',
          'price' => 33.00,
        ],
      ],
      'status' => 'active',
    ]);

    // 33 off 1 month shared
    Coupon::create([
      'code' => '33OFF1MS',
      'name' => '33% off for 1 month',
      'type' => Coupon::TYPE_SHARED,
      'coupon_event' => null,
      'discount_type' => Coupon::DISCOUNT_TYPE_PERCENTAGE,
      'percentage_off' => 33,
      'interval' => Coupon::INTERVAL_MONTH,
      'interval_count' => 1,
      'condition' => [
        "new_customer_only" => false,
        "new_subscription_only" => false,
        "upgrade_only" => false,
      ],
      'start_date' => '2023-01-01',
      'end_date' => '2099-12-31',
      'status' => 'active',
    ]);

    // 23 off 3 month shared
    Coupon::create([
      'code' => '23OFF3MS',
      'name' => '23% off for 3 months',
      'type' => Coupon::TYPE_SHARED,
      'coupon_event' => null,
      'discount_type' => Coupon::DISCOUNT_TYPE_PERCENTAGE,
      'percentage_off' => 23,
      'interval' => Coupon::INTERVAL_MONTH,
      'interval_count' => 3,
      'condition' => [
        "new_customer_only" => false,
        "new_subscription_only" => false,
        "upgrade_only" => false,
      ],
      'start_date' => '2023-01-01',
      'end_date' => '2099-12-31',
      'status' => 'active',
    ]);

    // 23 off 3 month once off
    Coupon::create([
      'code' => '23OFF3MO',
      'name' => '23% off for 3 months',
      'type' => Coupon::TYPE_ONCE_OFF,
      'coupon_event' => null,
      'discount_type' => Coupon::DISCOUNT_TYPE_PERCENTAGE,
      'percentage_off' => 23,
      'interval' => Coupon::INTERVAL_MONTH,
      'interval_count' => 3,
      'condition' => [
        "new_customer_only" => false,
        "new_subscription_only" => false,
        "upgrade_only" => false,
      ],
      'start_date' => '2023-01-01',
      'end_date' => '2099-12-31',
      'status' => 'active',
    ]);

    // 9 off 1 year shared
    Coupon::create([
      'code' => '9OFF1YS',
      'name' => '9% off for 1 year',
      'type' => Coupon::TYPE_SHARED,
      'coupon_event' => null,
      'discount_type' => Coupon::DISCOUNT_TYPE_PERCENTAGE,
      'percentage_off' => 9,
      'interval' => Coupon::INTERVAL_YEAR,
      'interval_count' => 1,
      'condition' => [
        "new_customer_only" => false,
        "new_subscription_only" => false,
        "upgrade_only" => false,
      ],
      'start_date' => '2023-01-01',
      'end_date' => '2099-12-31',
      'status' => 'active',
    ]);

    // free 2 day shared
    Coupon::create([
      'code' => 'FREE2DS',
      'name' => 'Leonardo™ Design Studio Pro 2-day Free Trial',
      'type' => Coupon::TYPE_SHARED,
      'coupon_event' => null,
      'discount_type' => Coupon::DISCOUNT_TYPE_FREE_TRIAL,
      'percentage_off' => 100,
      'interval' => Coupon::INTERVAL_DAY,
      'interval_count' => 2,
      'condition' => [
        "new_customer_only" => false,
        "new_subscription_only" => false,
        "upgrade_only" => false,
      ],
      'start_date' => '2023-01-01',
      'end_date' => '2099-12-31',
      'status' => 'active',
    ]);

    // free 2 day once off
    Coupon::create([
      'code' => 'FREE2DO',
      'name' => 'Leonardo™ Design Studio Pro 2-day Free Trial',
      'type' => Coupon::TYPE_ONCE_OFF,
      'coupon_event' => null,
      'discount_type' => Coupon::DISCOUNT_TYPE_FREE_TRIAL,
      'percentage_off' => 100,
      'interval' => Coupon::INTERVAL_DAY,
      'interval_count' => 2,
      'condition' => [
        "new_customer_only" => false,
        "new_subscription_only" => false,
        "upgrade_only" => false,
      ],
      'start_date' => '2023-01-01',
      'end_date' => '2099-12-31',
      'status' => 'active',
    ]);

    // free 3 day shared
    Coupon::create([
      'code' => 'FREE3DS',
      'name' => 'Leonardo™ Design Studio Pro 3-day Free Trial',
      'type' => Coupon::TYPE_SHARED,
      'coupon_event' => null,
      'discount_type' => Coupon::DISCOUNT_TYPE_FREE_TRIAL,
      'percentage_off' => 100,
      'interval' => Coupon::INTERVAL_DAY,
      'interval_count' => 3,
      'condition' => [
        "new_customer_only" => false,
        "new_subscription_only" => false,
        "upgrade_only" => false,
      ],
      'start_date' => '2023-01-01',
      'end_date' => '2099-12-31',
      'status' => 'active',
    ]);

    // free 3 day shared
    Coupon::create([
      'code' => 'FREE3MS',
      'name' => 'Leonardo™ Design Studio Pro 3-month Free Trial',
      'type' => Coupon::TYPE_SHARED,
      'coupon_event' => null,
      'discount_type' => Coupon::DISCOUNT_TYPE_FREE_TRIAL,
      'percentage_off' => 100,
      'interval' => Coupon::INTERVAL_MONTH,
      'interval_count' => 3,
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
