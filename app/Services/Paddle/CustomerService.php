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
  public function prepareData(BillingInfo $billingInfo, PaddleOperation $mode): CreateCustomer|UpdateCustomer
  {
    $data = [
      'email' => $billingInfo->email,
      'name' => $billingInfo->first_name . ' ' . $billingInfo->last_name,
      'customData' => CustomerCustomData::from([
        'user_id' => $billingInfo->user_id,
        'billing_info_id' => $billingInfo->id,
      ])->toCustomData()
    ];
    if ($mode === PaddleOperation::UPDATE) {
      $data['status'] = Status::Active();
    }
    return $mode === PaddleOperation::CREATE ? new CreateCustomer(...$data) : new UpdateCustomer(...$data);
  }

  /**
   * create customer from user and billing information
   */
  public function createPaddleCustomer(BillingInfo $billingInfo): Customer
  {
    try {
      $createCustomer = $this->prepareData($billingInfo, PaddleOperation::CREATE);
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

    $updateCustomer = $this->prepareData($billingInfo, PaddleOperation::UPDATE);
    return $this->paddleService->updateCustomer($meta->paddle->customer_id, $updateCustomer);
  }

  public function createOrUpdatePaddleCustomer(BillingInfo $billingInfo): Customer
  {
    return $billingInfo->getMeta()->paddle->customer_id ?
      $this->updatePaddleCustomer($billingInfo) :
      $this->createPaddleCustomer($billingInfo);
  }

  public function updateBillingInfo(BillingInfo $billingInfo, Customer $customer): BillingInfo
  {
    $billingInfo->setMetaPaddleCustomerId($customer->id)
      ->save();

    PaddleMap::createOrUpdate($customer->id, BillingInfo::class, $billingInfo->id, $customer->customData?->data);
    return $billingInfo;
  }

  public function getManagementLinks(BillingInfo $billingInfo): array
  {
    $session = $this->paddleService->getCustomerPortaSession($billingInfo->getMeta()->paddle->customer_id);
    return [
      'overview'  => $session->urls->general->overview,
    ];
  }
}
