<?php

return [
  'enabled' => env('FORCE_ENABLE') || env('APP_ENV') === 'production',

  // FirstPromoter API key
  'first_promoter' => [
    'api_key' => env('FIRST_PROMOTER_KEY', ''),
    'plan_mapping' => [
      // interval => type
      'month' => 'monthly-plan',
      'year' => 'annual-plan',
    ],
    'id_prefix' => env('APP_ENV') . '-',
  ],
];
