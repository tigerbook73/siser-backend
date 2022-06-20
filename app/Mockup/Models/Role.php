<?php

/**
 * Role
 */

namespace App\Mockup\Models;

/**
 * Role
 */
class Role
{
  /**
   * Possible values of this enum
   */
  const CUSTOMER = 'customer';

  const ADMIN = 'admin';

  const LDS = 'lds';

  const SISER_BACKEND = 'siser-backend';

  /**
   * Gets allowable values of the enum
   * @return string[]
   */
  public static function getAllowableEnumValues()
  {
    return [
      self::CUSTOMER,
      self::ADMIN,
      self::LDS,
      self::SISER_BACKEND
    ];
  }
}
