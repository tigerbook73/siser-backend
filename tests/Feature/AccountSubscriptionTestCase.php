<?php

namespace Tests\Feature;

use App\Models\Subscription;
use Tests\DR\DrApiTestCase;
use Tests\Models\Subscription as ModelsSubscription;

class AccountSubscriptionTestCase extends DrApiTestCase
{
  public string $baseUrl = '/api/v1/account/subscriptions';
  public string $model = Subscription::class;

  public Subscription $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsSubscription);
    unset($this->modelSchema[array_search('license_package_info', $this->modelSchema)]);
    unset($this->modelSchema[array_search('items', $this->modelSchema)]);
  }
}
