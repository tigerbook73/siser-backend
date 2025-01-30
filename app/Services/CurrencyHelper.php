<?php

namespace App\Services;

class CurrencyHelper
{
  static public function getCurrencyConfig(string $currency)
  {
    return config('currency')[$currency] ?? null;
  }

  static public function isSupportedCurrency(string $currency): bool
  {
    return isset(config('currency')[$currency]);
  }

  static public function getDecimalFactor(string $currency): int
  {
    $currencyConfig = self::getCurrencyConfig($currency);
    if (!$currencyConfig) {
      throw new \Exception("Currency not supported: {$currency}");
    }
    return $currencyConfig['factor'];
  }

  static public function getDecimalPrice(string $currency, string|int $price): float
  {
    return (int)$price / self::getDecimalFactor($currency);
  }

  static public function getLowestDenominationPrice(string $currency, float $price): string
  {
    // Round up to the nearest whole number
    return (string)(int)round($price * self::getDecimalFactor($currency), 0);
  }
}
