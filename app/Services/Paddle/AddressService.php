<?php

namespace App\Services\Paddle;

use App\Models\BillingInfo;
use App\Models\Paddle\AddressCustomData;
use App\Models\PaddleMap;
use Paddle\SDK\Entities\Address as Address;
use Paddle\SDK\Entities\Shared\CountryCode;
use Paddle\SDK\Notifications\Entities\Address as EntitiesAddress;
use Paddle\SDK\Resources\Addresses\Operations\CreateAddress;
use Paddle\SDK\Resources\Addresses\Operations\UpdateAddress;

/**
 * Paddle Address Service
 *
 * 1. address may updated from our side and the Paddle side
 * 2. when the billing info is updated, the paddle address is updated
 * 3. when a transaction is made, the billing info is updated from the address
 */
class AddressService extends PaddleEntityService
{
  /**
   * Prepare CreateAddress or UpdateAddress from billing information
   *
   * @param $billingInfo BillingInfo
   * @param $mode PaddleOperation
   */
  public function prepareData(BillingInfo $billingInfo, PaddleOperation $mode): CreateAddress|UpdateAddress
  {
    // for US postal code must be 5 digits, trucate if more than 5
    $postcode = $billingInfo->address['postcode'];
    if ($billingInfo->address['country'] === 'US') {
      $postcode = substr($postcode, 0, 5);
    }

    $data = [
      'countryCode' => CountryCode::from($billingInfo->address['country']),
      'firstLine' => $billingInfo->address['line1'] ?? "",
      'secondLine' => $billingInfo->address['line2'] ?? "",
      'city' => $billingInfo->address['city'] ?? "",
      'region' => $billingInfo->address['state'] ?? "",
      'postalCode' => $postcode,
      'customData' => AddressCustomData::from([
        'user_id' => $billingInfo->user_id,
        'billing_info_id' => $billingInfo->id,
      ])->toCustomData()
    ];
    return $mode === PaddleOperation::CREATE ? new CreateAddress(...$data) : new UpdateAddress(...$data);
  }

  /**
   * create address from billing information
   */
  public function createPaddleAddress(BillingInfo $billingInfo): Address
  {
    $meta = $billingInfo->getMeta();
    if (!$meta->paddle->customer_id) {
      throw new \Exception('Paddle customer not exist');
    }

    if (!$billingInfo->address['country'] || !$billingInfo->address['postcode']) {
      throw new \Exception('Country and postcode are required');
    }

    $createAddress = $this->prepareData($billingInfo, PaddleOperation::CREATE);
    $paddleaddress = $this->paddleService->createAddress(
      $billingInfo->getMeta()->paddle->customer_id,
      $createAddress
    );
    $this->updateBillingInfo($billingInfo, $paddleaddress);
    return $paddleaddress;
  }

  public function updatePaddleAddress(BillingInfo $billingInfo): Address
  {
    $meta = $billingInfo->getMeta();
    if (!$meta->paddle->customer_id) {
      throw new \Exception('Paddle customer not exist');
    }

    $updateAddress = $this->prepareData($billingInfo, PaddleOperation::UPDATE);
    return $this->paddleService->updateAddress(
      $meta->paddle->customer_id,
      $meta->paddle->address_id,
      $updateAddress
    );
  }

  public function createOrUpdatePaddleAddress(BillingInfo $billingInfo): Address
  {
    return $billingInfo->getMeta()->paddle->address_id ?
      $this->updatePaddleAddress($billingInfo) :
      $this->createPaddleAddress($billingInfo);
  }

  public function updateBillingInfo(BillingInfo $billingInfo, Address|EntitiesAddress $address): BillingInfo
  {
    $billingAddress = $billingInfo->address;
    $billingAddress['country']  = $address->countryCode->getValue();
    $billingAddress['line1']    = $address->firstLine ?? '';
    $billingAddress['line2']    = $address->secondLine ?? '';
    $billingAddress['city']     = $address->city ?? '';
    $billingAddress['state']    = $address->region ?? '';
    $billingAddress['postcode'] = $address->postalCode ?? '';
    $billingInfo->address = $billingAddress;
    $billingInfo->setMetaPaddleAddressId($address->id);
    $billingInfo->save();

    PaddleMap::createOrUpdate($address->id, BillingInfo::class, $billingInfo->id);
    return $billingInfo;
  }
}
