<?php

namespace App\Services;

enum CouponValidateResultCode: string
{
  case SUCCESS                          = 'success';
  case FAILED_INVALID_CODE              = 'invalid_code';
  case FAILED_INVALID_PLAN              = 'invalid_plan';
  case FAILED_INVALID_LICENSE_QUANTITY  = 'invalid_license_quantity';
  case FAILED_FREE_TRIAL_NOT_ALLOWED    = 'free_trial_not_allowed';
  case FAILED_FREE_TRIAL_MORE_THAN_ONCE = 'free_trial_more_than_once';
  case FAILED_NOT_APPLICABLE            = 'not_applicable';
}
