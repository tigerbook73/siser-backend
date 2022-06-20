<?php

/**
 * Plan
 */

namespace App\Mockup\Models;

use Faker\Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use stdClass;

/**
 * Plan
 */
class Repository
{
  /**  */
  public Generator $faker;

  /** */
  public $data = [];

  public function __construct()
  {
    $this->faker = app()->make(Generator::class);

    // restore data from files
    if ($this->unserialize()) {
      return;
    }

    $this->reset();
  }

  public function reset()
  {
    // remove file
    Storage::delete('repository.dat');

    $this->data = [
      Machine::class          => [],
      Plan::class             => [],
      SoftwarePackage::class  => [],
      Subscription::class     => [],
      UserX::class            => [],
      ConfigureGeneral::class => new ConfigureGeneral(),
    ];

    /**
     * create software package
     */
    $softwarePackage = new SoftwarePackage();
    $softwarePackage->name = "LDS software windows";
    $softwarePackage->platform = "windows";
    $softwarePackage->version = "5.0.1";
    $softwarePackage->description = "";
    $softwarePackage->version_type = "stable";
    $softwarePackage->released_date = "2022-06-19";
    $softwarePackage->release_notes = "https://www.google.com";
    $softwarePackage->filename = "package.zip";
    $softwarePackage->is_latest = true;
    $softwarePackage->url = "";
    $this->createSoftwarePackage($softwarePackage);

    /**
     * create default plan
     */
    $price = new PriceWithCurrency();
    $price->currency = 'USD';
    $price->price = 0.0;

    //
    $freePlan = new Plan();
    $freePlan->name                = 'Machine Basic Plan (free)';
    $freePlan->catagory            = 'machine';
    $freePlan->description         = 'Machine Basic Plan (free)';
    $freePlan->subscription_level  = 1;
    $freePlan->contract_term       = 'permanent';
    $freePlan->price               = [$price];
    $plan = $this->createPlan($freePlan);

    /**
     * create one test user
     */
    $user = $this->createUser();

    /**
     * create one machine
     */
    $machine = new Machine();
    $machine->serial_no = '0000-1111-2222-3333';
    $machine->model = 'Siser Cutter XY';
    $machine->manufacture = 'Siser';
    $machine->user_id = $user->id;
    $this->createMachine($machine);
  }


  public function serialize(): bool
  {
    $text = serialize($this->data);
    Storage::put('repository.dat', $text);

    return true;
  }

  public function unserialize(): bool
  {
    if (Storage::exists('repository.dat')) {
      $text = Storage::get('repository.dat');
      $this->data = unserialize($text);
      return true;
    }
    return false;
  }

  public function nextId(array $elements)
  {
    return end($elements) ? end($elements)->id + 1 : 1;
  }

  public function getElement(string $model, int $id)
  {
    if (!isset($this->data[$model][$id])) {
      abort(404, "Resource NotFound");
    }
    return $this->data[$model][$id];
  }

  public function getElements(string $model)
  {
    return array_values($this->data[$model]);
  }

  public function createUser()
  {
    $obj = new UserX();

    $obj->id                  = $this->nextId($this->data[UserX::class]);
    $obj->name                = $this->faker->name();
    $obj->email               = $this->faker->email();
    $obj->country             = $this->faker->country();
    $obj->language            = $this->faker->languageCode();
    $obj->cognito_id          = (string)$obj->id;
    $obj->subscription_level  = 0;
    $obj->roles               = [Role::CUSTOMER];
    $this->data[UserX::class][$obj->id] = $obj;

    $this->serialize();
    return $obj;
  }

  public function UpdateUser(int $id, $user)
  {
    $obj = $this->getUser($id);
    $obj->country  = $user->country;
    $obj->language = $user->language;

    $this->serialize();
    return $obj;
  }

  /**
   * @return UserX[]
   */
  public function getUsers()
  {
    return $this->getElements(UserX::class);
  }

  public function getUser(int $id): UserX
  {
    return $this->getElement(UserX::class, $id);
  }

  /**
   * @param Machine|stdClass $machine
   */
  public function createMachine($machine)
  {
    $obj = new Machine();

    $obj->id          = $this->nextId($this->data[Machine::class]);
    $obj->serial_no   = $machine->serial_no;
    $obj->model       = $machine->model;
    $obj->manufacture = $machine->manufacture;
    $obj->user_id     = $machine->user_id;

    $this->data[Machine::class][$obj->id] = $obj;

    $user = $this->getUser($machine->user_id);
    $user->subscription_level = 1;

    $this->createMachineSubscription($user->id);

    $this->serialize();
    return $obj;
  }

  public function deleteMachine(int $id): void
  {
    $machine = $this->getMachine($id);
    unset($this->data[Machine::class][$id]);

    $user = $this->getUser($machine->user_id);
    if (empty($this->getUserMachines($user->id))) {
      $subscription = $this->getUserSubscription($user->id);
      if ($subscription) {
        unset($this->data[Subscription::class][$subscription->id]);
      }
    }

    $this->serialize();
  }

  /**
   * @return Machine[]
   */
  public function getMachines()
  {
    return $this->getElements(Machine::class);
  }

  /**
   * @return Machine[]
   */
  public function getUserMachines(int $user_id)
  {
    $machines = $this->getElements(Machine::class);
    return array_filter($machines, fn ($machine) => $machine->user_id == $user_id);
  }

  public function getMachine(int $id): Machine
  {
    return $this->getElement(Machine::class, $id);
  }

  public function createPlan(Plan|stdClass $plan)
  {
    $obj = new Plan();

    $obj->id                  = $this->nextId($this->data[Plan::class]);
    $obj->name                = $plan->name;
    $obj->catagory            = $plan->catagory;
    $obj->description         = $plan->description;
    $obj->subscription_level  = $plan->subscription_level;
    $obj->contract_term       = $plan->contract_term;
    $obj->price               = $plan->price;
    $obj->auto_renew          = $plan->auto_renew;
    $obj->url                 = $plan->url;
    $obj->status              = 'active';

    $this->data[Plan::class][$obj->id] = $obj;

    $this->serialize();
    return $obj;
  }

  /**
   * @return SoftwarePackage[]
   */
  public function getPlans()
  {
    return $this->getElements(Plan::class);
  }

  public function getPlan(int $id): Plan
  {
    return $this->getElement(Plan::class, $id);
  }

  public function createSoftwarePackage(SoftwarePackage|stdClass $softwarePackage)
  {
    $obj = new SoftwarePackage();

    $obj->id            = $this->nextId($this->data[SoftwarePackage::class]);
    $obj->name          = $softwarePackage->name;
    $obj->platform      = $softwarePackage->platform;
    $obj->version       = $softwarePackage->version;
    $obj->description   = $softwarePackage->description;
    $obj->version_type  = $softwarePackage->version_type;
    $obj->released_date = $softwarePackage->released_date;
    $obj->release_notes = $softwarePackage->release_notes;
    $obj->filename      = $softwarePackage->filename;
    $obj->is_latest     = $softwarePackage->is_latest;
    $obj->url           = $softwarePackage->url;

    $this->data[SoftwarePackage::class][$obj->id] = $obj;

    $this->serialize();
    return $obj;
  }

  /**
   * @return SoftwarePackage[]
   */
  public function getSoftwarePackages()
  {
    return $this->getElements(SoftwarePackage::class);
  }

  public function getSoftwarePackage(int $id): SoftwarePackage
  {
    return $this->getElement(SoftwarePackage::class, $id);
  }

  protected function createMachineSubscription(int $user_id)
  {
    if ($subscription = $this->getUserSubscription($user_id)) {
      return $subscription;
    }

    $obj = new Subscription();

    $obj->id          = $this->nextId($this->data[Subscription::class]);
    $obj->user_id     = $user_id;
    $obj->plan        = $this->getPlan(1);
    $obj->start_date  = today();
    $obj->end_date    = null;
    $obj->status      = 'active';

    $this->data[Subscription::class][$obj->id] = $obj;

    $this->serialize();
    return $obj;
  }

  public function getUserSubscription(int $user_id): Subscription|null
  {
    $subscriptions = $this->getElements(Subscription::class);
    return array_filter($subscriptions, fn ($subscription) => $subscription->user_id == $user_id)[0] ?? null;
  }

  public function getConfigGeneral()
  {
    return $this->data[ConfigureGeneral::class];
  }

  public function updateConfigGeneral(ConfigureGeneral|stdClass $configGeneral)
  {
    $obj = $this->data[ConfigureGeneral::class];
    $obj->machine_license_unit = $configGeneral->machine_license_unit;
    return $obj;
  }
}
