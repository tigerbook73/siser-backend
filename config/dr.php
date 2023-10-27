<?php

return [
  /**
   * valid value: test, staging, prod
   */
  'dr_mode' => env('DR_MODE', 'test'),

  /**
   * DR host and token
   */
  'host' => env('DR_HOST', 'https://api.digitalriver.com'),
  'token' => env('DR_TOKEN'),

  /**
   * DR webhook
   */
  'default_webhook' => env('DR_DEFAULT_WEBHOOK'),

  /**
   * default SKUGroup id
   */
  'sku_grp_subscription' => env('DR_SKU_GRP_SUBSCRIPTION', 'software-subscription-01'),

  /**
   * tax rate pre-calculate checkout's upstream id
   */
  'tax_rate_pre_calcualte_id' => 'tax-rate-pre-calculation',

  /**
   * manual renewal reminer (offset from next_invoice_date)
   */
  'renewal' => [
    'start_offset'            =>  30,
    'expire_offset'           =>  2,
    'first_reminder_offset'   =>  30,
    'final_reminder_offset'   =>  5,
  ],
];
