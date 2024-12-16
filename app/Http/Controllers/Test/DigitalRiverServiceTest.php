<?php

namespace App\Http\Controllers\Test;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\DigitalRiver\DigitalRiverService;
use DigitalRiver\ApiSdk\Model\ChargeType as DrChargeType;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\GooglePay as DrGooglePay;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\Payments as DrPayments;
use DigitalRiver\ApiSdk\Model\Session as DrSession;
use DigitalRiver\ApiSdk\Model\SkuItem as DrSkuItem;
use DigitalRiver\ApiSdk\Model\Source as DrSource;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use DigitalRiver\ApiSdk\Model\Tax as DrTax;
use DigitalRiver\ApiSdk\Model\TaxIdentifier as DrTaxIdentifier;

class DigitalRiverServiceTest extends DigitalRiverService
{
  const TAX_RATE = 0.1;

  /**
   * @var array $drObjects $id => drObject
   */
  static public $drObjects = [];

  public function createCheckout(Invoice $invoice): DrCheckout
  {
    $checkout = new DrCheckout();

    /**
     * data from invoice
     */
    $checkout->setCustomerId((string)$invoice->user->getDrCustomerId());
    $checkout->setCustomerType($invoice->billing_info['customer_type']);
    $checkout->setEmail($invoice->billing_info['email']);
    $checkout->setLocale($invoice->billing_info['locale']);
    $checkout->setBillTo($this->fillBilling($invoice->billing_info));
    $checkout->setCurrency($invoice->currency);

    $checkout->setChargeType(DrChargeType::CUSTOMER_INITIATED); // @phpstan-ignore-line
    $checkout->setMetadata([
      'subscription_id' => $invoice->subscription_id,
      'invoice_id' => $invoice->id
    ]);
    $checkout->setUpstreamId((string)$invoice->id);

    // set tax id
    if (!empty($invoice->tax_id_info)) {
      $checkout->setTaxIdentifiers([(new DrTaxIdentifier())->setId($invoice->tax_id_info['dr_tax_id'])]);
    } else {
      $checkout->setTaxIdentifiers([]);
    }

    /**
     * fake data
     */
    $checkout->setId(uuid_create());
    $checkout->setBrowserIp('192.168.0.1');

    $paymentMethodInfo = $invoice->user->payment_method->info();
    $checkout->setPayment(
      (new DrPayments())
        ->setSession((new DrSession())->setId(uuid_create()))
        ->setSources([
          (new DrSource())
            ->setId(uuid_create())
            ->setType($paymentMethodInfo['type'])
            ->setGooglePay(
              (new DrGooglePay())
                ->setBrand($paymentMethodInfo['display_data']['brand'])
                ->setLastFourDigits($paymentMethodInfo['display_data']['last_four_digits'])
                ->setExpirationMonth($paymentMethodInfo['display_data']['expiration_month'])
                ->setExpirationYear($paymentMethodInfo['display_data']['expiration_year'])
            )
        ])
    );
    $checkoutItems = [];
    foreach ($this->fillCheckoutItems($invoice) as $item) {
      $checkoutItem = (new DrSkuItem())
        ->setProductDetails($item->getProductDetails())
        ->setQuantity($item->getQuantity())
        ->setAmount($item->getPrice())
        ->setTax(
          (new DrTax())
            ->setRate(self::TAX_RATE)
            ->setAmount(round($item->getPrice() * self::TAX_RATE, 2))
        );
      if ($item->getSubscriptionInfo()) {
        $checkoutItem->setSubscriptionInfo($item->getSubscriptionInfo()->setSubscriptionId(uuid_create()));
      }
      $checkoutItems[] = $checkoutItem;
    }
    $checkout->setItems($checkoutItems);
    $checkout->setSubtotal(round(array_reduce($checkoutItems, fn ($carry, $item) => $carry + $item->getAmount(), 0), 2));
    $checkout->setTotalTax(round(array_reduce($checkoutItems, fn ($carry, $item) => $carry + $item->getTax()->getAmount(), 0), 2));
    $checkout->setTotalAmount(round($checkout->getSubtotal() + $checkout->getTotalTax(), 2));

    return $checkout;
  }

  public function getSubscription(string $id): DrSubscription
  {
    return (new DrSubscription())->setId($id);
  }

  public function convertSubscriptionToNext(DrSubscription $drSubscription, Subscription $subscription): DrSubscription
  {
    return $drSubscription;
  }
}
