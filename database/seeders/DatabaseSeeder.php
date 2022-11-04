<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\LdsInstance;
use App\Models\LdsLog;
use App\Models\LdsPool;
use App\Models\LdsRegistration;
use App\Models\Machine;
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
    $cognitoUsers = (new CognitoProvider)->getSoftwareUserList();
    foreach ($cognitoUsers as $cognitoUser) {
      User::createOrUpdateFromCognitoUser($cognitoUser);
    }
    $customer = User::first();

    /**
     * create software packages
     */
    SoftwarePackage::create([
      'name'                => 'LDS software',
      'platform'            => 'Windows',
      'version'             => '0.0.1',
      'description'         => 'test data __test__',
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
      'description'         => 'test data __test__',
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
      'nickname'      => 'first machine __test__',
      'user_id'       => $customer->id,
    ]);
  }

  public function cleanData()
  {
    // 
    // clean test data
    // 

    /** @var int[] $userIds */
    $userIds = User::where('email', 'like', 'user%.test%')->get()->map(fn ($item) => $item->id);

    /** @var int[] $subscriptionIds */
    $subscriptionIds = Subscription::whereIn('user_id', $userIds)->get()->map(fn ($item) => $item->id);

    /** @var int[] $ldsPoolIds */
    $ldsPoolIds = LdsPool::whereIn('user_id', $userIds)->get()->map(fn ($item) => $item->id);

    /** @var int[] $ldsRegistrationIds */
    $ldsRegistrationIds = LdsRegistration::whereIn('user_id', $userIds)->get()->map(fn ($item) => $item->id);

    /** @var int[] $ldsInstanceIds */
    $ldsInstanceIds = LdsInstance::whereIn('user_id', $userIds)->get()->map(fn ($item) => $item->id);

    /** @var int[] $machineIds */
    $machineIds = Machine::where('nickname', 'like', '%__test__%')->get()->map(fn ($item) => $item->id);

    /** @var int[] $plans */
    $planIds = Plan::where('name', 'like', '%__test__%')->get()->map(fn ($item) => $item->id);

    /** @var int[] $softwarePackageIds */
    $softwarePackageIds = SoftwarePackage::where('description', 'like', '%__test__%')->get()->map(fn ($item) => $item->id);

    /** @var AdminUser[]|Collection $users */
    $adminUserIds = AdminUser::where('name', 'like', '%__test__%')->get();

    // remove user related data
    LdsLog::whereIn('lds_instance_id', $ldsInstanceIds)->delete();
    LdsInstance::whereIn('id', $ldsInstanceIds)->delete();
    LdsRegistration::whereIn('id', $ldsRegistrationIds)->delete();
    Machine::whereIn('id', $machineIds)->delete();
    LdsPool::whereIn('id', $ldsPoolIds)->delete();
    Subscription::whereIn('id', $subscriptionIds)->delete();
    User::whereIn('id', $userIds)->delete();

    // remove test softare packages
    SoftwarePackage::destroy($softwarePackageIds);

    // remove test plans
    Plan::whereIn('id', $planIds)->delete();

    // remove test admin users
    AdminUser::whereIn('id', $adminUserIds)->delete();
  }
}
