<?php

namespace App\Services\Paddle;

use Exception;
use Illuminate\Support\Collection;
use Paddle\SDK\Client;
use Paddle\SDK\Entities\Address;
use Paddle\SDK\Entities\Adjustment;
use Paddle\SDK\Entities\Business;
use Paddle\SDK\Entities\Customer;
use Paddle\SDK\Entities\CustomerPortalSession;
use Paddle\SDK\Entities\Discount;
use Paddle\SDK\Entities\Product;
use Paddle\SDK\Entities\Price;
use Paddle\SDK\Entities\Subscription;
use Paddle\SDK\Entities\Subscription\SubscriptionEffectiveFrom;
use Paddle\SDK\Entities\Transaction;
use Paddle\SDK\Environment;
use Paddle\SDK\Notifications\Secret;
use Paddle\SDK\Notifications\Verifier;
use Paddle\SDK\Options;
use Paddle\SDK\Resources\Addresses\Operations\CreateAddress;
use Paddle\SDK\Resources\Addresses\Operations\UpdateAddress;
use Paddle\SDK\Resources\Adjustments\Operations\CreateAdjustment;
use Paddle\SDK\Resources\Adjustments\Operations\ListAdjustments;
use Paddle\SDK\Resources\Businesses\Operations\CreateBusiness;
use Paddle\SDK\Resources\Businesses\Operations\UpdateBusiness;
use Paddle\SDK\Resources\CustomerPortalSessions\Operations\CreateCustomerPortalSession;
use Paddle\SDK\Resources\Customers\Operations\CreateCustomer;
use Paddle\SDK\Resources\Customers\Operations\ListCustomers;
use Paddle\SDK\Resources\Customers\Operations\UpdateCustomer;
use Paddle\SDK\Resources\Discounts\Operations\CreateDiscount;
use Paddle\SDK\Resources\Discounts\Operations\UpdateDiscount;
use Paddle\SDK\Resources\NotificationSettings\Operations\UpdateNotificationSetting;
use Paddle\SDK\Resources\Prices\Operations\CreatePrice;
use Paddle\SDK\Resources\Prices\Operations\ListPrices;
use Paddle\SDK\Resources\Prices\Operations\UpdatePrice;
use Paddle\SDK\Resources\Products\Operations\CreateProduct;
use Paddle\SDK\Resources\Products\Operations\ListProducts;
use Paddle\SDK\Resources\Products\Operations\UpdateProduct;
use Paddle\SDK\Resources\Subscriptions\Operations\CancelSubscription;
use Paddle\SDK\Resources\Subscriptions\Operations\Get\Includes as SubscriptionIncludes;
use Paddle\SDK\Resources\Subscriptions\Operations\UpdateSubscription;
use Paddle\SDK\Resources\Transactions\Operations\List\Includes as TransactionIncludes;

class PaddleService
{
  public Client $paddle;

  public function __construct()
  {
    $this->paddle = new Client(
      apiKey: config('paddle.api_key'),
      options: new Options(Environment::from(config('paddle.environment')))
    );
  }

  protected function throwException(\Throwable $th, string $level = 'error'): Exception
  {
    return new Exception("TODO", 500);
  }

  public function verifyWebhook($request): bool
  {
    return (new Verifier())->verify($request, new Secret(config('paddle.webhook_secret')));
  }

  /**
   * customers
   */
  public function createCustomer(CreateCustomer $createCustomer): Customer
  {
    return $this->paddle->customers->create($createCustomer);
  }

  public function updateCustomer(string $id, UpdateCustomer $updateCustomer): Customer
  {
    return $this->paddle->customers->update($id, $updateCustomer);
  }

  public function getCustomer(string $id): Customer
  {
    return $this->paddle->customers->get($id);
  }

  public function findCustomerByEmail(string $email): ?Customer
  {
    $customers = $this->paddle->customers->list(new ListCustomers(emails: [$email]));
    if ($customers->valid()) {
      return $customers->current();
    } {
      return null;
    }
  }

  /**
   * address
   */

  public function createAddress(string $customerId, CreateAddress $createAddress): Address
  {
    return $this->paddle->addresses->create($customerId, $createAddress);
  }

  public function updateAddress(string $customerId, string $id, UpdateAddress $updateAddress): Address
  {
    return $this->paddle->addresses->update($customerId, $id, $updateAddress);
  }


  /**
   * business
   */
  public function createBusiness(string $customerId, CreateBusiness $createBusiness): Business
  {
    return $this->paddle->businesses->create($customerId, $createBusiness);
  }

  public function updateBusiness(string $customerId, string $id, UpdateBusiness $updateBusiness): business
  {
    return $this->paddle->businesses->update($customerId, $id, $updateBusiness);
  }

  /**
   * products
   */

  /**
   * @return Collection<int, Product>
   */
  public function listProducts(ListProducts $listOperation = new ListProducts()): Collection
  {
    $products = $this->paddle->products->list($listOperation);
    return new Collection($products);
  }

  public function updateProduct(string $id, UpdateProduct $updateOperation): Product
  {
    return $this->paddle->products->update($id, $updateOperation);
  }

  public function createProduct(CreateProduct $createProduct): Product
  {
    return $this->paddle->products->create($createProduct);
  }

  public function archiveProduct(string $id): Product
  {
    return $this->paddle->products->archive($id);
  }

  /**
   * prices
   */

  /**
   * @return Collection<int, Price>
   */
  public function listPrices(ListPrices $listOperation = new ListPrices()): Collection
  {
    $prices = $this->paddle->prices->list($listOperation);
    return new Collection($prices);
  }

  public function getPrice(string $id): Price
  {
    return $this->paddle->prices->get($id);
  }

  public function updatePrice(string $id, UpdatePrice $updateOperation): Price
  {
    return $this->paddle->prices->update($id, $updateOperation);
  }

  public function createPrice(CreatePrice $createPrice): Price
  {
    return $this->paddle->prices->create($createPrice);
  }

  public function archivePrice(string $id): Price
  {
    return $this->paddle->prices->archive($id);
  }

  /**
   * discounts
   */

  public function getDiscount(string $id): Discount
  {
    return $this->paddle->discounts->get($id);
  }

  public function updateDiscount(string $id, UpdateDiscount $updateOperation): Discount
  {
    return $this->paddle->discounts->update($id, $updateOperation);
  }

  public function createDiscount(CreateDiscount $createDiscount): Discount
  {
    return $this->paddle->discounts->create($createDiscount);
  }

  public function archiveDiscount(string $id): Discount
  {
    return $this->paddle->discounts->archive($id);
  }


  /**
   * Payment Methods
   */

  /**
   * Subscriptons
   */

  public function getSubscriptionWithIncludes(string $id): Subscription
  {
    return $this->paddle->subscriptions->get($id, [
      SubscriptionIncludes::NextTransaction(),
      SubscriptionIncludes::RecurringTransactionDetails(),
    ]);
  }

  public function getSubscription(string $id): Subscription
  {
    return $this->paddle->subscriptions->get($id);
  }

  public function cancelSubscription(string $id, bool $immediate = false): Subscription
  {
    return $this->paddle->subscriptions->cancel(
      $id,
      new CancelSubscription(
        $immediate ?
          SubscriptionEffectiveFrom::Immediately() :
          SubscriptionEffectiveFrom::NextBillingPeriod()
      )
    );
  }

  public function removeSubscriptionScheduledChange(string $id): Subscription
  {
    return $this->paddle->subscriptions->update($id, new UpdateSubscription(
      scheduledChange: null,
    ));
  }

  /**
   * Transactions
   */
  public function getTransaction(string $id): Transaction
  {
    return $this->paddle->transactions->get($id, [
      TransactionIncludes::Address(),
      TransactionIncludes::Adjustment(),
      TransactionIncludes::AdjustmentsTotals(),
      TransactionIncludes::AvailablePaymentMethods(),
      TransactionIncludes::Business(),
      TransactionIncludes::Customer(),
      TransactionIncludes::Discount(),
    ]);
  }

  public function getTransactionInvoicePdf(string $id): string
  {
    $transactionData = $this->paddle->transactions->getInvoicePDF($id);
    return $transactionData->url;
  }

  /**
   * Refund
   */
  public function createAdjustment(CreateAdjustment $createAdjustment): Adjustment
  {
    return $this->paddle->adjustments->create($createAdjustment);
  }

  public function getAdjustment(string $id): Adjustment
  {
    $adjustments = $this->paddle->adjustments->list(new ListAdjustments(ids: [$id]));
    if ($adjustments->valid()) {
      return $adjustments->current();
    } {
      throw new Exception('Not found!', 404);
    }
  }

  /**
   * customer portal
   *
   * @param string $customerId
   * @param string[] $subscripitonIds
   */
  public function getCustomerPortaSession(string $customerId, array $subscripitonIds = []): CustomerPortalSession
  {
    return $this->paddle->customerPortalSessions->create($customerId, new CreateCustomerPortalSession($subscripitonIds));
  }

  /**
   * Notification
   */

  public function updateDefaultWebhook(array $events, bool $enable): void
  {
    $this->paddle->notificationSettings->update(
      config('paddle.webhook_id'),
      new UpdateNotificationSetting(
        subscribedEvents: $events,
        active: $enable,
      )
    );
  }

  public function replayNotification(string $id): string
  {
    return $this->paddle->notifications->replay($id);
  }
}
