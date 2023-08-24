<?php

return [
  // siser's cognito configuration
  'cognito' => [
    'user_pool_id'  => env("COGNITO_USER_POOL_ID"),
    'region'        => env('COGNITO_REGION'),
    'key_id'        => env('COGNITO_ACCESS_KEY_ID'),
    'key_secret'    => env('COGNITO_SECRET_ACCESS_KEY'),
  ],

  // sis configuration
  'login_uri'       => '/auth/login',
  'sign_in_uri'     => env('SIGN_IN_URI', 'https://auth.siser.com'),
  'sign_out_uri'    => env('SIGN_OUT_URI', 'https://auth.siser.com/'),

  // in environment
  'aws_region'      => env('AWS_REGION'),
  'aws_key_id'      => env('AWS_ACCESS_KEY_ID'),
  'aws_key_secret'  => env('AWS_SECRET_ACCESS_KEY'),

  'plan' => [
    'default_machine_plan' => 1,
  ],

  'support_email'   => "support@siserna.com",
  'bcc_email'       => env('APP_TEST_CODE') ? 'user2.test@iifuture.com' : 'siser-notifications@fcl.software',

  // staff can refund multiple times
  'staff_refund'    => true,

  // adjust user type by email domain
  'staff_emails_auto' => true,
  'staff_emails'    => [
    '@fcl.software',
    '@iifuture.com',
    '@siser.com',
    '@siseranz.com',
    '@siserasia.com',
    '@siserna.com',
  ]
];
