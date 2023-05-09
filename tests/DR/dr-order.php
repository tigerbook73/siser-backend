<?php

return <<<EOD
{
  "id": "248797780336",
  "createdTime": "2022-12-12T02:57:05Z",
  "customerId": "user1-test",
  "currency": "USD",
  "email": "user1.test@iifuture.com",
  "billTo": {
      "address": {
          "line1": "16 Byward St",
          "city": "London",
          "postalCode": "EC3R 5BA",
          "state": "England",
          "country": "GB"
      },
      "name": "John Doe",
      "phone": "952-253-1234",
      "email": "test@test.com"
  },
  "totalAmount": 22.8,
  "subtotal": 19.0,
  "totalFees": 0.0,
  "totalTax": 3.8,
  "totalImporterTax": 0.0,
  "totalDuty": 0.0,
  "totalDiscount": 0.0,
  "totalShipping": 0.0,
  "items": [
      {
          "id": "177858250336",
          "skuId": "PRO-0001",
          "productDetails": {
              "id": "PRO-0001",
              "name": "Pro Service",
              "eccn": "EAR99",
              "taxCode": "4323.320_A",
              "description": "LDS Pro Service",
              "countryOfOrigin": "US"
          },
          "amount": 19.0,
          "quantity": 1,
          "state": "fulfilled",
          "stateTransitions": {
              "created": "2022-12-12T02:57:06Z",
              "fulfilled": "2022-12-12T02:57:27Z"
          },
          "tax": {
              "rate": 0.2,
              "amount": 3.8
          },
          "importerTax": {
              "amount": 0.0
          },
          "duties": {
              "amount": 0.0
          },
          "subscriptionInfo": {
              "subscriptionId": "b2239289-4e1d-4919-82fc-7bfecb97c5c3",
              "planId": "PRO-PLAN-0004",
              "terms": "These are the terms....",
              "autoRenewal": true,
              "freeTrial": false,
              "billingAgreementId": "d5428a50-9ce5-4d88-b46e-3bad328f0d37"
          },
          "availableToRefundAmount": 22.8,
          "fees": {
              "amount": 0.0,
              "taxAmount": 0.0
          },
          "sellerTaxIdentifier": "GB892232909",
          "shipping": {
              "amount": 0.0,
              "taxAmount": 0.0
          }
      }
  ],
  "updatedTime": "2022-12-12T04:01:12Z",
  "locale": "en_US",
  "customerType": "individual",
  "sellingEntity": {
      "id": "DR_UK-ENTITY",
      "name": "Digital River UK"
  },
  "liveMode": false,
  "payment": {
      "charges": [
          {
              "id": "9cbf06db-e557-48de-9022-ad527e483229",
              "createdTime": "2022-12-12T02:57:11Z",
              "currency": "USD",
              "amount": 22.8,
              "state": "complete",
              "captured": true,
              "captures": [
                  {
                      "id": "b4d08579-147c-484a-a1af-947450b50da2",
                      "createdTime": "2022-12-12T02:57:27Z",
                      "amount": 22.8,
                      "state": "complete"
                  }
              ],
              "refunded": false,
              "sourceId": "900afde8-5be1-4639-add4-cc266d45347b",
              "type": "customer_initiated"
          }
      ],
      "sources": [
          {
              "id": "900afde8-5be1-4639-add4-cc266d45347b",
              "type": "creditCard",
              "amount": 22.8,
              "owner": {
                  "firstName": "John",
                  "lastName": "Doe",
                  "email": "test@test.com",
                  "address": {
                      "line1": "16 Byward St",
                      "city": "London",
                      "postalCode": "EC3R 5BA",
                      "state": "England",
                      "country": "GB"
                  }
              },
              "creditCard": {
                  "brand": "Visa",
                  "expirationMonth": 12,
                  "expirationYear": 2032,
                  "lastFourDigits": "1111"
              }
          }
      ],
      "session": {
          "id": "4aede589-b04e-45d2-a3f2-4c2ee079d937",
          "amountContributed": 22.8,
          "amountRemainingToBeContributed": 0.0,
          "state": "complete",
          "clientSecret": "4aede589-b04e-45d2-a3f2-4c2ee079d937_4b9095e0-0b51-4022-9c95-686f7263f417"
      }
  },
  "state": "complete",
  "stateTransitions": {
      "fulfilled": "2022-12-12T02:57:27Z",
      "accepted": "2022-12-12T02:57:12Z",
      "complete": "2022-12-12T02:58:20Z"
  },
  "fraudStateTransitions": {
      "passed": "2022-12-12T02:57:12Z"
  },
  "requestToBeForgotten": false,
  "capturedAmount": 22.8,
  "cancelledAmount": 0.0,
  "availableToRefundAmount": 22.8,
  "invoicePDFs": [
      {
          "id": "0d7f174a-0e60-4a0d-8727-7d8a9f1a2232",
          "url": "https://api.digitalriver.com/files/0d7f174a-0e60-4a0d-8727-7d8a9f1a2232/content",
          "liveMode": false
      },
      {
          "id": "8ee874d3-1a3d-437c-b758-c6e01799efc8",
          "url": "https://api.digitalriver.com/files/8ee874d3-1a3d-437c-b758-c6e01799efc8/content",
          "liveMode": false
      }
  ],
  "checkoutId": "48e2796b-ec21-4c28-b283-61aa255d23f4"
}
EOD;
