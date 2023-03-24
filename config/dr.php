<?php

return [
  'token' => env('DR_TOKEN'),
  'host' => env('DR_HOST', 'https://api.digitalriver.com'),
  'default_plan' => env('DR_DEFAULT_PLAN', 'default-plan-id'),
  'default_webhook' => env('DR_DEFAULT_WEBHOOK'),
  'sku_grp_subscription' => env('DR_SKU_GRP_SUBSCRIPTION', 'software-subscription-01'),
  'sku_grp_process_fee' => env('DR_SKU_GRP_SUBSCRIPTION', 'processing-fee-01'),
];
