<?php

namespace Tests\Trait;

use App\Models\BillingInfo;
use App\Models\Plan;
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
use Paddle\SDK\Entities\DateTime;
use Paddle\SDK\Entities\Price;
use Paddle\SDK\Entities\Shared\Status;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

function serialize($data)
{
  $serializer = new Serializer(
    [
      new BackedEnumNormalizer(),
      new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => DateTime::PADDLE_RFC3339]),
      new JsonSerializableNormalizer(),
      new ObjectNormalizer(nameConverter: new CamelCaseToSnakeCaseNameConverter()),
    ],
    [new JsonEncoder()],
  );

  return $serializer->serialize($data, 'json', [
    AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true,
  ]);
}

class AddressService extends AddressServiceStandard
{
  public function fake(BillingInfo $billingInfo, $mode): Address
  {
    $data = json_decode(serialize($this->prepareData($billingInfo, $mode)), true);
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
    $address = $this->fake($billingInfo, 'create');
    $this->updateBillingInfo($billingInfo, $address);
    return $address;
  }

  public function updatePaddleAddress(BillingInfo $billingInfo): Address
  {
    $address = $this->fake($billingInfo, 'update');
    $this->updateBillingInfo($billingInfo, $address);
    return $address;
  }
}

class BusinessService extends BusinessServiceStandard {}

class CustomerService extends CustomerServiceStandard
{
  public function fake(BillingInfo $billingInfo, $mode): Customer
  {
    $data = json_decode(serialize($this->prepareData($billingInfo, $mode)), true);
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
    $customer = $this->fake($billingInfo, 'create');
    $this->updateBillingInfo($billingInfo, $customer);
    return $customer;
  }

  public function updatePaddleCustomer(BillingInfo $billingInfo): Customer
  {
    $customer = $this->fake($billingInfo, 'update');
    $this->updateBillingInfo($billingInfo, $customer);
    return $customer;
  }
}

class DiscountService extends DiscountServiceStandard {}

class PaymentMethodService extends PaymentMethodServiceStandard {}

class PriceService extends PriceServiceStandard
{
  /**
   * @param Plan $plan
   * @param string $mode - create|update
   */
  public function fake(Plan $plan, string $mode): Price
  {
    // prepare product
    $product = $plan->product;
    $product->setMetaPaddleProductId("pro_{$product->id}")->save();

    $price = json_decode(serialize($this->prepareData($plan, $mode)), true);
    return Price::from(
      [
        ...$price,
        'id' => "pri_{$plan->id}",
        'product_id' => $product->getMeta()->paddle->product_id ?? "pro_{$product->id}",
        'status' => $price['status'] ?? Status::Active()->getValue(),
        'created_at' => $plan->created_at,
        'updated_at' => $plan->updated_at,
      ]
    );
  }

  public function createPaddlePrice(Plan $plan): Price
  {
    $paddlePrice = $this->fake($plan, 'create');
    $this->updatePlan($plan, $paddlePrice);
    return $paddlePrice;
  }

  public function updatePaddlePrice(Plan $plan): Price
  {
    $updatePrice = $this->fake($plan, 'update');
    $this->updatePlan($plan, $updatePrice);
    return $updatePrice;
  }
}

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
