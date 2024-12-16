<?php

namespace App\Services;

class CountryHelper
{
  static public function getRequiredAddressItems(string $country): array
  {
    $countryConfig = config('country.supported_countries')[$country] ?? null;
    return $countryConfig['required_address_items'] ?? [];
  }

  static public function isSupportedCountry(string $country): bool
  {
    return isset(config('country.supported_countries')[$country]);
  }

  static public function isEuCountry(string $country): bool
  {
    return isset(config('country.eu_countries')[$country]);
  }

  static public function getEUCountryTaxRate(string $country): float
  {
    $countryConfig = config('country.eu_countries')[$country] ?? null;
    if (!$countryConfig) {
      throw new \Exception("Country not found in EU countries: {$country}");
    }
    return $countryConfig['tax_rate'];
  }
}
