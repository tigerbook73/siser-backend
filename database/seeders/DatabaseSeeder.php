<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\BillingInfo;
use App\Models\Invoice;
use App\Models\LdsInstance;
use App\Models\LdsLog;
use App\Models\LdsPool;
use App\Models\LdsRegistration;
use App\Models\Machine;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\SoftwarePackage;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Cognito\CognitoProvider;
use Illuminate\Database\Eloquent\Collection;
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
    $this->cleanData();
    $this->createTestData();
  }

  public function createTestData()
  {
    /**
     * create users
     */
    $customer = User::createOrUpdateFromCognitoUser((new CognitoProvider)->getUserByName('user1.test'));

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


    Machine::create([
      'serial_no'     => '0000-1111-2222-3333',
      'model'         => 'Siser Cutter XY',
      'nickname'      => '__test__',
      'user_id'       => $customer->id,
    ]);
  }

  public function cleanData()
  {
    // 
    // clean test data
    // 

    /** @var int[] $userIds */
    $userIds = User::where('email', 'like', 'user%.test%')->get()->modelKeys();

    /** @var int[] $billingInfoIds */
    $billingInfoIds = BillingInfo::whereIn('user_id', $userIds)->get()->modelKeys();

    /** @var int[] $paymentMethodIds */
    $paymentMethodIds = PaymentMethod::whereIn('user_id', $userIds)->get()->modelKeys();

    /** @var int[] $subscriptionIds */
    $subscriptionIds = Subscription::whereIn('user_id', $userIds)->get()->modelKeys();

    /** @var int[] $invoiceIds */
    $invoiceIds = Invoice::whereIn('user_id', $userIds)->get()->modelKeys();

    /** @var int[] $ldsPoolIds */
    $ldsPoolIds = LdsPool::whereIn('user_id', $userIds)->get()->modelKeys();

    /** @var int[] $ldsRegistrationIds */
    $ldsRegistrationIds = LdsRegistration::whereIn('user_id', $userIds)->get()->modelKeys();

    /** @var int[] $ldsInstanceIds */
    $ldsInstanceIds = LdsInstance::whereIn('user_id', $userIds)->get()->modelKeys();

    /** @var int[] $machineIds */
    $machineIds = Machine::where('nickname', 'like', '%\_\_test\_\_%')->get()->modelKeys();

    /** @var int[] $plans */
    $planIds = Plan::where('name', 'like', '%\_\_test\_\_%')->get()->modelKeys();

    /** @var int[] $softwarePackageIds */
    $softwarePackageIds = SoftwarePackage::where('description', 'like', '%\_\_test\_\_%')->get()->modelKeys();

    /** @var AdminUser[]|Collection $users */
    $adminUserIds = AdminUser::where('name', 'like', '%\_\_test\_\_%')->get()->modelKeys();

    // remove user related data
    LdsLog::whereIn('lds_instance_id', $ldsInstanceIds)->delete();
    LdsInstance::whereIn('id', $ldsInstanceIds)->delete();
    LdsRegistration::whereIn('id', $ldsRegistrationIds)->delete();
    Machine::whereIn('id', $machineIds)->delete();
    LdsPool::whereIn('id', $ldsPoolIds)->delete();
    Invoice::whereIn('id', $invoiceIds)->delete();
    Subscription::whereIn('id', $subscriptionIds)->delete();
    BillingInfo::whereIn('id', $billingInfoIds)->delete();
    PaymentMethod::whereIn('id', $paymentMethodIds)->delete();
    User::whereIn('id', $userIds)->delete();

    // remove test softare packages
    SoftwarePackage::destroy($softwarePackageIds);

    // remove test plans
    Plan::whereIn('id', $planIds)->delete();

    // remove test admin users
    AdminUser::whereIn('id', $adminUserIds)->delete();
  }
}
