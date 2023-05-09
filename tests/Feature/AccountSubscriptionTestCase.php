<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use Tests\ApiTestCase;
use Tests\DR\DrTestTrait;
use Tests\Models\Subscription as ModelsSubscription;
use Tests\Trait\TestCreateTrait;

class AccountSubscriptionTestCase extends ApiTestCase
{
  use DrTestTrait, TestCreateTrait;

  public string $baseUrl = '/api/v1/account/subscriptions';
  public string $model = Subscription::class;

  public Subscription $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsSubscription);
  }
}
