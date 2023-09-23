<?php

return <<<EOD
{
  "id": "16b57ee1-4615-480c-8fdf-c48520118c11",
  "createdTime": "2023-05-02T01:57:28Z",
  "customerId": "617854820336",
  "currency": "AUD",
  "email": "user1.test@iifuture.com",
  "billTo": {
    "address": {
      "line1": "abc",
      "city": "mel",
      "postalCode": "3000",
      "state": "vic",
      "country": "AU"
    },
    "name": "User1 Test",
    "phone": "+61400000000",
    "email": "user1.test@iifuture.com"
  },
  "totalAmount": 15.4,
  "subtotal": 14.0,
  "totalFees": 0.0,
  "totalTax": 1.4,
  "totalImporterTax": 0.0,
  "totalDuty": 0.0,
  "totalDiscount": 0.0,
  "totalShipping": 0.0,
  "items": [
    {
      "id": "f13e6c3a-e340-497b-b5a5-d3cd503ad71a",
      "productDetails": {
        "name": "Leonardo™ Design Studio Pro Monthly Plan",
        "eccn": "EAR99",
        "taxCode": "4323.320_A",
        "skuGroupId": "software-subscription-01",
        "countryOfOrigin": "AU"
      },
      "amount": 14.0,
      "quantity": 1,
      "tax": {
        "rate": 0.1,
        "amount": 1.4
      },
      "subscriptionInfo": {
        "subscriptionId": "e5ad8813-69bf-4515-ba5a-d399ee0efc98",
        "planId": "test-3-day",
        "autoRenewal": true,
        "freeTrial": false,
        "billingAgreementId": "7019f4be-fdb3-4416-8d26-60b670a6c480"
      }
    }
  ],
  "updatedTime": "2023-05-03T01:39:25Z",
  "locale": "en_US",
  "customerType": "individual",
  "chargeType": "merchant_initiated",
  "sellingEntity": {
    "id": "DR_IRELAND-ENTITY",
    "name": "Digital River Ireland Ltd."
  },
  "liveMode": false,
  "payment": {
    "charges": [
      {
        "id": "f59a8439-cc38-437c-8a9e-8c1ca755b46a",
        "createdTime": "2023-05-03T01:39:24Z",
        "currency": "AUD",
        "amount": 15.4,
        "state": "complete",
        "captured": true,
        "captures": [
          {
            "id": "4590a40b-fea1-4a20-b814-3980d44a871f",
            "createdTime": "2023-05-03T01:39:27Z",
            "amount": 15.4,
            "state": "complete"
          }
        ],
        "refunded": false,
        "sourceId": "411e8194-bce6-4b52-a8e2-4dc06639c792",
        "type": "merchant_initiated"
      }
    ],
    "sources": [
      {
        "id": "411e8194-bce6-4b52-a8e2-4dc06639c792",
        "type": "payPalBilling",
        "amount": 15.4,
        "owner": {
          "firstName": "User1",
          "lastName": "Test",
          "email": "user1.test@iifuture.com",
          "address": {
            "line1": "abc",
            "city": "mel",
            "postalCode": "3000",
            "state": "vic",
            "country": "AU"
          }
        },
        "payPalBilling": {
          "token": "12345",
          "payerStatus": "verified",
          "payerId": "payerId",
          "redirectUrl": "https://api.digitalriverws.com:443/payments/redirects/3b7fe1d1-9f55-4a27-92c1-c83f546d1eb0?apiKey=pk_test_9b43a854a1ce49caa1b5f6e208602adc",
          "returnUrl": "https://js.digitalriverws.com/v1/1.20230413.1840/components/paypal-receiver/paypal-receiver.html?componentId=paypal-db1c540a-dae9-4193-b296-dbf1be3e4a00&controllerId=controller-6274881a-8a81-4624-95a2-9cf75a223659&action=return&type=paypal",
          "offline": false
        }
      }
    ],
    "session": {
      "id": "e8a745cd-5dac-4af4-8ac2-73194c21c556",
      "amountContributed": 15.4,
      "amountRemainingToBeContributed": 0.0,
      "state": "complete",
      "clientSecret": "e8a745cd-5dac-4af4-8ac2-73194c21c556_e8bef20a-b5d5-4860-887d-af4764c2f7fd"
    }
  }
}
EOD;
