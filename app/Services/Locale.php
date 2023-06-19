<?php

namespace App\Services;

class Locale
{
  static public $languages = [
    'zh' => 'Chinese',
    'cs' => 'Czech',
    'da' => 'Danish',
    'nl' => 'Dutch',
    'en' => 'English',
    'fi' => 'Finnish',
    'fr' => 'French',
    'de' => 'German',
    'it' => 'Italian',
    'jp' => 'Japanese',
    'ko' => 'Korean',
    'nn' => 'Norwegian',
    'pl' => 'Polish',
    'ro' => 'Romanian',
    'es' => 'Spanish',
    'sv' => 'Swedish',
  ];

  static public $countrie = [
    'AT' => 'Austria',
    'BE' => 'Belgium',
    'CA' => 'Canada',
    'CH' => 'Switzerland',
    'CN' => 'China',
    'CZ' => 'Czechia',
    'DE' => 'Germany',
    'DK' => 'Denmark',
    'ES' => 'Spain',
    'FI' => 'Finland',
    'FR' => 'France',
    'IT' => 'Italy',
    'JP' => 'Japan',
    'KR' => 'Korea',
    'NL' => 'Netherlands',
    'NO' => 'Norway',
    'PL' => 'Poland',
    'RO' => 'Romania',
    'SE' => 'Sweden',
    'TW' => 'Taiwan, Province of China',
    'US' => 'United States of America',
  ];

  static public $countryLanguages = [
    // default is 'en'
    'AT' =>  ['default' => 'de', 'all' => ['de']],
    'BE' =>  ['default' => 'nl', 'all' => ['fr', 'nl']],
    'CA' =>  ['default' => 'en', 'all' => ['fr']],
    'CH' =>  ['default' => 'de', 'all' => ['de', 'fr', 'it']],
    'CN' =>  ['default' => 'zh', 'all' => ['zh']],
    'CZ' =>  ['default' => 'cs', 'all' => ['cs']],
    'DE' =>  ['default' => 'de', 'all' => ['de']],
    'DK' =>  ['default' => 'da', 'all' => ['da']],
    'ES' =>  ['default' => 'es', 'all' => ['es']],
    'FI' =>  ['default' => 'fi', 'all' => ['fi']],
    'FR' =>  ['default' => 'fr', 'all' => ['fr']],
    'IT' =>  ['default' => 'it', 'all' => ['it']],
    'JP' =>  ['default' => 'ja', 'all' => ['ja']],
    'KR' =>  ['default' => 'ko', 'all' => ['ko']],
    'NL' =>  ['default' => 'nl', 'all' => ['nl']],
    'NO' =>  ['default' => 'no', 'all' => ['no']],
    'PL' =>  ['default' => 'pl', 'all' => ['pl']],
    'RO' =>  ['default' => 'ro', 'all' => ['ro']],
    'SE' =>  ['default' => 'sv', 'all' => ['sv']],
    'TW' =>  ['default' => 'zh', 'all' => ['zh']],
    'US' =>  ['default' => 'en', 'all' => ['en']],
  ];

  // data from https://docs.digitalriver.com/digital-river-api/integration-options/checkouts/creating-checkouts/designating-a-locale
  static public $locales = [
    'de_AT' =>  ['language' => 'de', 'country' => 'AT'],
    'fr_BE' =>  ['language' => 'fr', 'country' => 'BE'],
    'nl_BE' =>  ['language' => 'nl', 'country' => 'BE'],
    'fr_CA' =>  ['language' => 'fr', 'country' => 'CA'],
    'de_CH' =>  ['language' => 'de', 'country' => 'CH'],
    'fr_CH' =>  ['language' => 'fr', 'country' => 'CH'],
    'it_CH' =>  ['language' => 'it', 'country' => 'CH'],
    'zh_CN' =>  ['language' => 'zh', 'country' => 'CN'],
    'cs_CZ' =>  ['language' => 'cs', 'country' => 'CZ'],
    'de_DE' =>  ['language' => 'de', 'country' => 'DE'],
    'da_DK' =>  ['language' => 'da', 'country' => 'DK'],
    'es_ES' =>  ['language' => 'es', 'country' => 'ES'],
    'fi_FI' =>  ['language' => 'fi', 'country' => 'FI'],
    'fr_FR' =>  ['language' => 'fr', 'country' => 'FR'],
    'it_IT' =>  ['language' => 'it', 'country' => 'IT'],
    'ja_JP' =>  ['language' => 'ja', 'country' => 'JP'],
    'ko_KR' =>  ['language' => 'ko', 'country' => 'KR'],
    'nl_NL' =>  ['language' => 'nl', 'country' => 'NL'],
    'no_NO' =>  ['language' => 'no', 'country' => 'NO'],
    'pl_PL' =>  ['language' => 'pl', 'country' => 'PL'],
    'ro_RO' =>  ['language' => 'ro', 'country' => 'RO'],
    'sv_SE' =>  ['language' => 'sv', 'country' => 'SE'],
    'zh_TW' =>  ['language' => 'zh', 'country' => 'TW'],
    'en_US' =>  ['language' => 'en', 'country' => 'US'],
  ];

  static public function locale(string $languageCode, string $countryCode): string
  {
    $locale = $languageCode . '_' . $countryCode;
    return (isset(self::$locales[$locale])) ? $locale : 'en_US';
  }

  static public function languages(string $country): array
  {
    return self::$countryLanguages[$country]['all'] ?? ['en'];
  }

  static public function defaultLanguage(string $country, string $suggestedLanguage = null): string
  {
    if ($suggestedLanguage && in_array($suggestedLanguage, self::$countryLanguages[$country]['all'] ?? [])) {
      return $suggestedLanguage;
    }
    return self::$countryLanguages[$country]['default'] ?? 'en';
  }

  static public function defaultLocale(string $country): string
  {
    return self::locale(self::defaultLanguage($country), $country);
  }
}
