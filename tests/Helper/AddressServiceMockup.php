<?php

namespace Tests\Helper;

use App\Models\BillingInfo;
use App\Services\Paddle\AddressService;
use App\Services\Paddle\PaddleOperation;
use Paddle\SDK\Entities\Address;
use Paddle\SDK\Entities\Shared\Status;

class AddressServiceMockup extends AddressService
{
  public function fake(BillingInfo $billingInfo, PaddleOperation $mode): Address
  {
    $data = json_decode(PaddleTestHelper::serialize($this->prepareData($billingInfo, $mode)), true);
    return Address::from(
      [
        ...$data,
        'id' => "add_{$billingInfo->id}",
        'customer_id' => $billingInfo->getMeta()->paddle->customer_id,
        'status' => $data['status'] ?? Status::Active()->getValue(),
        'created_at' => $billingInfo->created_at,
        'updated_at' => $billingInfo->updated_at,
      ]
    );
  }

  public function createPaddleAddress(BillingInfo $billingInfo): Address
  {
    $address = $this->fake($billingInfo, PaddleOperation::CREATE);
    $this->updateBillingInfo($billingInfo, $address);
    return $address;
  }

  public function updatePaddleAddress(BillingInfo $billingInfo): Address
  {
    $address = $this->fake($billingInfo, PaddleOperation::UPDATE);
    $this->updateBillingInfo($billingInfo, $address);
    return $address;
  }
}
