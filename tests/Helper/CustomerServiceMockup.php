<?php

namespace Tests\Helper;

use App\Models\BillingInfo;
use App\Services\Paddle\CustomerService;
use App\Services\Paddle\PaddleOperation;
use Paddle\SDK\Entities\Customer;
use Paddle\SDK\Entities\Shared\Status;

class CustomerServiceMockup extends CustomerService
{
  public function fake(BillingInfo $billingInfo, PaddleOperation $mode): Customer
  {
    $data = json_decode(PaddleTestHelper::serialize($this->prepareData($billingInfo, $mode)), true);
    return Customer::from(
      [
        ...$data,
        'id' => "ctm_{$billingInfo->id}",
        'email' => $billingInfo->email,
        'marketing_consent' => false,
        'status' => $data['status'] ?? Status::Active()->getValue(),
        'locale' => 'en-US',
        'created_at' => $billingInfo->created_at,
        'updated_at' => $billingInfo->updated_at,
      ]
    );
  }

  /**
   * create customer from user and billing information
   */
  public function createPaddleCustomer(BillingInfo $billingInfo): Customer
  {
    $customer = $this->fake($billingInfo, PaddleOperation::CREATE);
    $this->updateBillingInfo($billingInfo, $customer);
    return $customer;
  }

  public function updatePaddleCustomer(BillingInfo $billingInfo): Customer
  {
    $customer = $this->fake($billingInfo, PaddleOperation::UPDATE);
    $this->updateBillingInfo($billingInfo, $customer);
    return $customer;
  }
}
