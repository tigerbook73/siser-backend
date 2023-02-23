<?php

namespace Tests\DR;

use App\Models\User;
use DigitalRiver\ApiSdk\Configuration;
use DigitalRiver\ApiSdk\Api\CheckoutsApi;
use DigitalRiver\ApiSdk\Api\SKUsApi;
use DigitalRiver\ApiSdk\Model\CheckoutRequest;
use DigitalRiver\ApiSdk\Model\Address;
use DigitalRiver\ApiSdk\Model\Billing;
use DigitalRiver\ApiSdk\Model\SkuRequestItem;
use DigitalRiver\ApiSdk\Model\SubscriptionInfo;
use GuzzleHttp\Client;
use Exception;

class DrTest
{
  public function test()
  {
    $config = Configuration::getDefaultConfiguration()->setAccessToken('sk_test_dc25334eed6a4fff8ad1516920379189');
    $config->setHost('https://api.digitalriver.com');

    $apiSku = new SKUsApi(new Client(), $config);
    $apiCheckout = new CheckoutsApi(new Client(), $config);

    try {
      /**
       * create & delete checkout test
       */

      /** @var User $user */
      $user = User::first();
      $address = new Address([
        'postal_code' => "3000",
        'state' => 'VIC',
        'country' => 'AU',
      ]);
      $billingTo = new Billing([
        'address' => $address,
        'name' => $user->given_name . ' ' . $user->family_name,
        'phone' => $user->phone_number,
        'email' => $user->email,
      ]);
      $subscription = new SubscriptionInfo([
        'plan_id' => 'PLAN-0001',
        'terms' => "These are the terms....",
      ]);
      $item = new SkuRequestItem([
        'sku_id' => 'SKU-0001',
        'quantity' => 1,
        'price' => 9.9,
        'subscription_info'  => $subscription,
      ]);
      $checkout_request = new CheckoutRequest(
        [
          'customer_id'       => 'user1-test',
          'email'             => 'null@digitalriver.com',
          'bill_to'           => $billingTo,
          'currency'          => 'USD',
          'items'             => [$item],
          'subscriptionInfo'  => $subscription,
        ],
      );

      $checkout = $apiCheckout->createCheckouts($checkout_request);
      printf('check data: ');
      print_r(json_encode($checkout, JSON_PRETTY_PRINT));
      $apiCheckout->deleteCheckouts($checkout->getId());

      $skuList = $apiSku->listSkus();
      printf('Sku data: ');
      print_r(json_encode($skuList, JSON_PRETTY_PRINT));
    } catch (Exception $e) {
      echo 'Exception when calling api: ', $e->getMessage(), PHP_EOL;
    }
  }
}
