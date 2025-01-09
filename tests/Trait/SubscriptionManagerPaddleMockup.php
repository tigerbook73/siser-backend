<?php

namespace Tests\Trait;

use App\Models\BillingInfo;
use App\Services\DigitalRiver\SubscriptionManagerResult;
use App\Services\LicenseSharing\LicenseSharingService;
use App\Services\Paddle\AddressService as AddressServiceStandard;
use App\Services\Paddle\BusinessService as BusinessServiceStandard;
use App\Services\Paddle\CustomerService as CustomerServiceStandard;
use App\Services\Paddle\DiscountService as DiscountServiceStandard;
use App\Services\Paddle\PaddleService;
use App\Services\Paddle\PaymentMethodService as PaymentMethodServiceStandard;
use App\Services\Paddle\PriceService as PriceServiceStandard;
use App\Services\Paddle\ProductService as ProductServiceStandard;
use App\Services\Paddle\SubscriptionService as SubscriptionServiceStandard;
use App\Services\Paddle\TransactionService as TransactionServiceStandard;
use App\Services\Paddle\SubscriptionManagerPaddle as SubscriptionManagerPaddleStandard;
use Paddle\SDK\Entities\Address;
use Paddle\SDK\Entities\Customer;

class AddressService extends AddressServiceStandard
{
  public function fake(BillingInfo $billingInfo): Address
  {
    return Address::from(
      [
        'id' => "adr_{$billingInfo->id}",
        'customer_id' => $billingInfo->getMeta()->paddle->customer_id,
        'first_line' => $billingInfo->address['line1'],
        'second_line' => $billingInfo->address['line2'],
        'city' => $billingInfo->address['city'],
        'postal_code' => $billingInfo->address['postcode'],
        'region' => $billingInfo->address['state'],
        'country_code' => $billingInfo->address['country'],
        'custom_data' => [
          'user_id' => $billingInfo->user_id,
          'billing_info_id' => $billingInfo->id,
        ],
        'status' => 'active',
        'created_at' => $billingInfo->created_at,
        'updated_at' => $billingInfo->updated_at,
      ]
    );
  }

  public function createPaddleAddress(BillingInfo $billingInfo): Address
  {
    $address = $this->fake($billingInfo);
    $this->updateBillingInfo($billingInfo, $address);
    return $address;
  }

  public function updatePaddleAddress(BillingInfo $billingInfo): Address
  {
    $address = $this->fake($billingInfo);
    $this->updateBillingInfo($billingInfo, $address);
    return $address;
  }
}

class BusinessService extends BusinessServiceStandard {}

class CustomerService extends CustomerServiceStandard
{
  public function fake(BillingInfo $billingInfo): Customer
  {
    return Customer::from(
      [
        'id' => "ctm_{$billingInfo->id}",
        'email' => $billingInfo->email,
        'marketing_consent' => false,
        'status' => 'active',
        'custom_data' => [
          'user_id' => $billingInfo->user_id,
          'billing_info_id' => $billingInfo->id,
        ],
        "locale" => "en-US",
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
    $customer = $this->fake($billingInfo);
    $this->updateBillingInfo($billingInfo, $customer);
    return $customer;
  }

  public function updatePaddleCustomer(BillingInfo $billingInfo): Customer
  {
    $customer = $this->fake($billingInfo);
    $this->updateBillingInfo($billingInfo, $customer);
    return $customer;
  }
}

class DiscountService extends DiscountServiceStandard {}

class PaymentMethodService extends PaymentMethodServiceStandard {}

class PriceService extends PriceServiceStandard {}

class ProductService extends ProductServiceStandard {}

class SubscriptionService extends SubscriptionServiceStandard {}

class TransactionService extends TransactionServiceStandard {}


class SubscriptionManagerPaddle extends SubscriptionManagerPaddleStandard
{
  public function __construct(
    public PaddleService $paddleService,
    public LicenseSharingService $licenseService,
    public SubscriptionManagerResult $result,
  ) {
    parent::__construct($paddleService, $licenseService, $result);

    $this->addressService       = new AddressService($this);
    $this->businessService      = new BusinessService($this);
    $this->customerService      = new CustomerService($this);
    $this->discountService      = new DiscountService($this);
    $this->paymentMethodService = new PaymentMethodService($this);
    $this->priceService         = new PriceService($this);
    $this->productService       = new ProductService($this);
    $this->subscriptionService  = new SubscriptionService($this);
    $this->transactionService   = new TransactionService($this);
  }
}

trait SubscriptionManagerPaddleMockup
{
  public function setupSubscriptionManagerPaddleMockup()
  {
    app()->bind(SubscriptionManagerPaddleStandard::class, SubscriptionManagerPaddle::class);
  }
}
