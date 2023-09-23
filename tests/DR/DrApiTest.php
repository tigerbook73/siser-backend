<?php

namespace Tests\DR;

use App\Models\BillingInfo;
use App\Models\Subscription;
use App\Services\DigitalRiver\DigitalRiverService;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\Customer as DrCustomer;
use DigitalRiver\ApiSdk\Model\FileLink as DrFileLink;
use DigitalRiver\ApiSdk\Model\Fulfillment as DrFulfillment;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\Source as DrSource;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;

class DrApiTest extends DrApiTestCase
{
  public function testDrApiMock()
  {
    /** @var DigitalRiverService $drService */
    $drService = $this->app->make(DigitalRiverService::class);

    /** @var Subscription $subscription */
    $subscription = Subscription::where('status', Subscription::STATUS_ACTIVE)->first();
    $billingInfo = new BillingInfo([
      'first_name' => "User1",
      'last_name' => "Test",
      'phone' => "+61400000000",
      'email' => "user1.test@iifuture.com",
      'address' => [
        "city" => "melbourne",
        "line1" => "123 abc st",
        "state" => "vic",
        "country" => "AU",
        "postcode" => "3000",
      ],
    ]);
    $id = "9999";

    // mockup
    $this->mockGetCustomer()
      ->mockCreateCustomer()
      ->mockUpdateCustomer()
      ->mockAttachCustomerSource()
      ->mockDetachCustomerSource()
      ->mockGetCheckout()
      ->mockCreateCheckout()
      ->mockUpdateCheckoutTerms()
      ->mockDeleteCheckout()
      ->mockAttachCheckoutSource()
      ->mockGetSource()
      ->mockGetOrder()
      ->mockConvertCheckoutToOrder()
      ->mockFulfillOrder()
      ->mockGetSubscription()
      ->mockActivateSubscription()
      ->mockDeleteSubscription()
      ->mockUpdateSubscriptionSource()
      ->mockUpdateSubscriptionItems()
      ->mockCancelSubscription()
      ->mockCreateFileLink();

    $this->assertTrue($drService->getCustomer($id) instanceof DrCustomer);
    $this->assertTrue($drService->createCustomer($billingInfo) instanceof DrCustomer);
    $this->assertTrue($drService->updateCustomer($id, $billingInfo) instanceof DrCustomer);
    $this->assertTrue($drService->attachCustomerSource($id, $id) instanceof DrSource);
    $this->assertTrue(is_bool($drService->detachCustomerSource($id, $id)));
    $this->assertTrue($drService->getCheckout($id) instanceof DrCheckout);
    $this->assertTrue($drService->createCheckout($subscription) instanceof DrCheckout);
    $this->assertTrue($drService->updateCheckoutTerms($id, "terms") instanceof  DrCheckout);
    $this->assertTrue(is_bool($drService->deleteCheckout($id)));
    $this->assertTrue($drService->attachCheckoutSource($id, $id) instanceof DrSource);
    $this->assertTrue($drService->getSource($id) instanceof DrSource);
    $this->assertTrue($drService->getOrder($id) instanceof  DrOrder);
    $this->assertTrue($drService->convertCheckoutToOrder($id) instanceof DrOrder);
    $this->assertTrue($drService->fulfillOrder($id) instanceof DrFulfillment);
    $this->assertTrue($drService->getSubscription($id) instanceof DrSubscription);
    $this->assertTrue($drService->activateSubscription($id) instanceof DrSubscription);
    $this->assertTrue(is_bool($drService->deleteSubscription($id)));
    $this->assertTrue($drService->updateSubscriptionSource($id, $id) instanceof DrSubscription);
    $this->assertTrue($drService->updateSubscriptionItems($id, $subscription) instanceof DrSubscription);
    $this->assertTrue($drService->cancelSubscription($id) instanceof DrSubscription);
    $this->assertTrue($drService->createFileLink($id, now()) instanceof DrFileLink);
  }
}
