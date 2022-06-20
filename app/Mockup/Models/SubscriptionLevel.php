<?php

/**
 * SubscriptionLevel
 */

namespace App\Mockup\Models;

/**
 * SubscriptionLevel
 */
class SubscriptionLevel
{
  /**
   * Possible values of this enum
   */
  const NUMBER_0 = 0;

  const NUMBER_1 = 1;

  const NUMBER_2 = 2;

  const NUMBER_3 = 3;

  /**
   * Gets allowable values of the enum
   * @return int[]
   */
  public static function getAllowableEnumValues()
  {
    return [
      self::NUMBER_0,
      self::NUMBER_1,
      self::NUMBER_2,
      self::NUMBER_3
    ];
  }
}
