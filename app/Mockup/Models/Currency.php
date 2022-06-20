<?php

/**
 * Currency
 */

namespace App\Mockup\Models;

/**
 * Currency
 */
class Currency
{
  /**
   * Possible values of this enum
   */
  const USD = 'USD';

  const AUD = 'AUD';

  const GBP = 'GBP';

  /**
   * Gets allowable values of the enum
   * @return string[]
   */
  public static function getAllowableEnumValues()
  {
    return [
      self::USD,
      self::AUD,
      self::GBP
    ];
  }
}
