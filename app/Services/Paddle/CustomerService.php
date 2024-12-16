<?php

namespace App\Services\Paddle;

use App\Models\BillingInfo;
use App\Models\Paddle\CustomerCustomData;
use App\Models\PaddleMap;
use Paddle\SDK\Entities\Customer;
use Paddle\SDK\Entities\Shared\Status;
use Paddle\SDK\Exceptions\ApiError\CustomerApiError;
use Paddle\SDK\Notifications\Entities\Customer as EntitiesCustomer;
use Paddle\SDK\Resources\Customers\Operations\CreateCustomer;
use Paddle\SDK\Resources\Customers\Operations\UpdateCustomer;

class CustomerService extends PaddleEntityService
{
  /**
   * create customer from user and billing information
   */
  public function createPaddleCustomer(BillingInfo $billingInfo): Customer
  {
    $createCustomer = new CreateCustomer(
      email: $billingInfo->email,
      name: $billingInfo->first_name . ' ' . $billingInfo->last_name,
      customData: CustomerCustomData::from([
        'user_id' => $billingInfo->user_id,
        'billing_info_id' => $billingInfo->id,
      ])->toCustomData()
    );

    try {
      $paddleCustomer = $this->paddleService->createCustomer($createCustomer);
      $this->updateBillingInfo($billingInfo, $paddleCustomer);
      return $paddleCustomer;
    } catch (CustomerApiError $e) {
      // if customer already exists, update customer
      if ($e->errorCode === 'customer_already_exists') {
        preg_match('/ctm_[a-zA-Z0-9]+/', $e->detail, $matches);
        $id = $matches[0] ?? null;
        if (!$id) {
          throw $e;
        }

        // prefill $billingInfo->meta->customer_id
        $billingInfo->setMetaPaddleCustomerId($id);
        $paddleCustomer = $this->updatePaddleCustomer($billingInfo);
        $this->updateBillingInfo($billingInfo, $paddleCustomer);
        return $paddleCustomer;
      } else {
        throw $e;
      }
    }
  }

  public function updatePaddleCustomer(BillingInfo $billingInfo): Customer
  {
    $meta = $billingInfo->getMeta();
    if (!$meta->paddle->customer_id) {
      throw new \Exception('Paddle customer not exist');
    }

    $updateCustomer = new UpdateCustomer(
      email: $billingInfo->email,
      name: $billingInfo->last_name . ' ' . $billingInfo->first_name,
      customData: CustomerCustomData::from([
        'user_id' => $billingInfo->user_id,
        'billing_info_id' => $billingInfo->id,
      ])->toCustomData(),
      status: Status::Active(),
    );

    return $this->paddleService->updateCustomer($meta->paddle->customer_id, $updateCustomer);
  }

  public function createOrUpdatePaddleCustomer(BillingInfo $billingInfo): Customer
  {
    return $billingInfo->getMeta()->paddle->customer_id ?
      $this->updatePaddleCustomer($billingInfo) :
      $this->createPaddleCustomer($billingInfo);
  }

  public function updateBillingInfo(BillingInfo $billingInfo, Customer|EntitiesCustomer|string $customer): BillingInfo
  {
    $customerId = gettype($customer) === 'string' ? $customer : $customer->id;
    $billingInfo->setMetaPaddleCustomerId($customerId)
      ->save();

    PaddleMap::createOrUpdate($customerId, BillingInfo::class, $billingInfo->id);
    return $billingInfo;
  }
}
