<?php

namespace App\Services\Paddle;

use App\Models\BillingInfo;
use App\Models\Paddle\BusinessCustomData;
use App\Models\PaddleMap;
use Paddle\SDK\Entities\Business as Business;
use Paddle\SDK\Notifications\Entities\Business as EntitiesBusiness;
use Paddle\SDK\Notifications\Events\BusinessCreated;
use Paddle\SDK\Notifications\Events\BusinessUpdated;

/**
 * Paddle Bussiness Service
 *
 * Paddle Business life cycle
 *
 * 1. initially created from TaxId
 * 2. Then tax id information is always updated from business object
 *    updated from subscription creation
 *    updated from business create/update
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

  /**
   * event handler for business model
   */
  public function onBusinessCreatedOrUpdated(BusinessCreated|BusinessUpdated $businessCreatedOrUpdated)
  {
    $business = $businessCreatedOrUpdated->business;
    $customData = BusinessCustomData::from($business->customData->data);
    $billingInfo = BillingInfo::findById($customData->billing_info_id);
    if (!$billingInfo) {
      $customer = $this->paddleService->getCustomer($business->customerId);
      $customData = BusinessCustomData::from($customer->customData->data);
      $billingInfo = BillingInfo::findById($customData->billing_info_id);
    }

    if (!$billingInfo) {
      throw new \Exception('Billing info not found');
    }

    $this->updateBillingInfo($billingInfo, $business);
  }
}
