<?php

namespace App\Services\Paddle;

use App\Models\BillingInfo;
use App\Models\Paddle\BusinessCustomData;
use App\Models\PaddleMap;
use App\Models\TaxId;
use Paddle\SDK\Entities\Business as Business;
use Paddle\SDK\Notifications\Entities\Business as EntitiesBusiness;
use Paddle\SDK\Notifications\Events\BusinessCreated;
use Paddle\SDK\Notifications\Events\BusinessUpdated;
use Paddle\SDK\Resources\Businesses\Operations\CreateBusiness;
use Paddle\SDK\Resources\Businesses\Operations\UpdateBusiness;

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
  /**
   * create business from billing information
   */
  public function createPaddleBusiness(BillingInfo $billingInfo): Business
  {
    $meta = $billingInfo->getMeta();
    if (!$meta->paddle->customer_id) {
      throw new \Exception('Paddle customer not exist');
    }

    /** @var ?TaxId @taxId */
    $taxId = TaxId::where('user_id', $billingInfo->user_id)->first();
    if (!$taxId) {
      throw new \Exception('Tax ID not exist');
    }

    $createBusiness = new CreateBusiness(
      name: $billingInfo->organization,
      taxIdentifier: $taxId->value,
      customData: BusinessCustomData::from([
        'user_id' => $billingInfo->user_id,
        'billing_info_id' => $billingInfo->id,
      ])->toCustomData()
    );

    $paddleBusiness = $this->paddleService->createBusiness(
      $meta->paddle->customer_id,
      $createBusiness
    );

    $this->updateBillingInfo($billingInfo, $paddleBusiness);
    return $paddleBusiness;
  }

  public function updatePaddleBusiness(BillingInfo $billingInfo): Business
  {
    $meta = $billingInfo->getMeta();
    if (!$meta->paddle->customer_id) {
      throw new \Exception('Paddle customer not exist');
    }
    if (!$meta->paddle->business_id) {
      throw new \Exception('Paddle business not exist');
    }

    /** @var ?TaxId @taxId */
    $taxId = TaxId::where('user_id', $billingInfo->user_id)->first();
    if (!$taxId) {
      throw new \Exception('Tax ID not exist');
    }

    $updateBusiness = new UpdateBusiness(
      name: $billingInfo->organization,
      taxIdentifier: $taxId->value,
      customData: BusinessCustomData::from([
        'user_id' => $billingInfo->user_id,
        'billing_info_id' => $billingInfo->id,
      ])->toCustomData()
    );

    return $this->paddleService->updateBusiness(
      $meta->paddle->customer_id,
      $meta->paddle->business_id,
      $updateBusiness
    );
  }

  public function createOrUpdatePaddleBusiness(BillingInfo $billingInfo): Business
  {
    return $billingInfo->getMeta()->paddle->business_id ?
      $this->updatePaddleBusiness($billingInfo) :
      $this->createPaddleBusiness($billingInfo);
  }

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
