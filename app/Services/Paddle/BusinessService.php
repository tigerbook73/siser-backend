<?php

namespace App\Services\Paddle;

use App\Models\BillingInfo;
use App\Models\PaddleMap;
use Paddle\SDK\Entities\Business as Business;
use Paddle\SDK\Notifications\Entities\Business as EntitiesBusiness;

/**
 * Paddle Bussiness Service
 *
 * 1. business only update from Paddle side
 * 2. when a transaction is made, the business id is set in the billing info
 */
class BusinessService extends PaddleEntityService
{
  public function updateBillingInfo(BillingInfo $billingInfo, Business|EntitiesBusiness $business): BillingInfo
  {
    $billingInfo->organization = $business->name;
    $billingInfo->setMetaPaddleBusinessId($business->id);
    $billingInfo->save();

    PaddleMap::createOrUpdate($business->id, BillingInfo::class, $billingInfo->id);
    return $billingInfo;
  }
}
