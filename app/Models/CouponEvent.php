<?php

namespace App\Models;

use App\Models\Base\CouponEvent as BaseCouponEvent;

class CouponEvent extends BaseCouponEvent
{
  static public function deleteNotUsed()
  {
    self::whereDoesntHave('coupons')->delete();
  }
}
