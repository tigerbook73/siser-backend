<?php

return <<<EOD
{
  "object": {
    "id": "81420da3-fb74-4572-8a03-ada46d6e757b",
    "createdTime": "2023-05-04T04:03:15Z",
    "type": "creditCard",
    "currency": "AUD",
    "amount": 15.4,
    "reusable": false,
    "state": "chargeable",
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
    "paymentSessionId": "1118688a-017e-4ecc-a92a-437061aee4e9",
    "clientSecret": "81420da3-fb74-4572-8a03-ada46d6e757b_cd01342c-156c-4022-b08d-3deb13942cb2",
    "creditCard": {
      "brand": "Visa",
      "expirationMonth": 11,
      "expirationYear": 2088,
      "lastFourDigits": "4103"
    },
    "liveMode": false
  }
}
EOD;
