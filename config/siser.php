<?php

return [
  // siser's cognito configuration
  'cognito' => [
    'user_pool_id'  => env("COGNITO_USER_POOL_ID"),
    'client_id'     => env('COGNITO_CLIENT_ID'),
    'client_secret' => env('COGNITO_CLIENT_SECRET'),
  ],

  // sis configuration
  'login_uri'       => '/auth/login',
  'sign_in_uri'     => env('SIGN_IN_URI', 'https://auth.siser.com'),
  'sign_out_uri'    => env('SIGN_OUT_URI', 'https://auth.siser.com/'),

  'domain_cookie'   => 'siser',
  'domain_env'      => (env('APP_ENV') === 'production') ? 'production' : 'sandbox',

  // in environment
  'aws_region'      => env('AWS_DEFAULT_REGION'),
  // 'aws_key_id' => null,
  // 'aws_key_secret' => null,

  'plan' => [
    'default_machine_plan' => 1,
  ]
];
