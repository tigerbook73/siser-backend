<?php

namespace App\Services;

class Locale
{
  // supported language
  static public $languages = [
    // 'zh' => 'Chinese',
    // 'cs' => 'Czech',
    // 'da' => 'Danish',
    // 'nl' => 'Dutch',
    'en' => 'English',
    // 'fi' => 'Finnish',
    'fr' => 'French',
    'de' => 'German',
    'it' => 'Italian',
    // 'jp' => 'Japanese',
    // 'ko' => 'Korean',
    // 'nn' => 'Norwegian',
    // 'pl' => 'Polish',
    // 'ro' => 'Romanian',
    'es' => 'Spanish',
    // 'sv' => 'Swedish',
  ];

  // supported countries in digital river locale
  // static public $countries = [
  //   'AT' => 'Austria',
  //   'BE' => 'Belgium',
  //   'CA' => 'Canada',
  //   'CH' => 'Switzerland',
  //   'CN' => 'China',
  //   'CZ' => 'Czechia',
  //   'DE' => 'Germany',
  //   'DK' => 'Denmark',
  //   'ES' => 'Spain',
  //   'FI' => 'Finland',
  //   'FR' => 'France',
  //   'IT' => 'Italy',
  //   'JP' => 'Japan',
  //   'KR' => 'Korea',
  //   'NL' => 'Netherlands',
  //   'NO' => 'Norway',
  //   'PL' => 'Poland',
  //   'RO' => 'Romania',
  //   'SE' => 'Sweden',
  //   'TW' => 'Taiwan, Province of China',
  //   'US' => 'United States of America',
  // ];

  // supported countries languages in digital river locale (default is 'en')
  static public $countryLanguages = [
    // default is 'en'
    'AT' =>  ['default' => 'de', 'all' => ['de', 'en']],
    'BE' =>  ['default' => 'fr' /* 'nl' */, 'all' => ['fr' /*, 'nl'*/, 'en']],
    'CA' =>  ['default' => 'en', 'all' => ['fr', 'en']],
    'CH' =>  ['default' => 'de', 'all' => ['de', 'fr', 'it', 'en']],
    // 'CN' =>  ['default' => 'zh', 'all' => ['zh', 'en']],
    // 'CZ' =>  ['default' => 'cs', 'all' => ['cs', 'en']],
    'DE' =>  ['default' => 'de', 'all' => ['de', 'en']],
    // 'DK' =>  ['default' => 'da', 'all' => ['da', 'en']],
    'ES' =>  ['default' => 'es', 'all' => ['es', 'en']],
    // 'FI' =>  ['default' => 'fi', 'all' => ['fi', 'en']],
    'FR' =>  ['default' => 'fr', 'all' => ['fr', 'en']],
    'IT' =>  ['default' => 'it', 'all' => ['it', 'en']],
    // 'JP' =>  ['default' => 'ja', 'all' => ['ja', 'en']],
    // 'KR' =>  ['default' => 'ko', 'all' => ['ko', 'en']],
    // 'NL' =>  ['default' => 'nl', 'all' => ['nl', 'en']],
    // 'NO' =>  ['default' => 'no', 'all' => ['no', 'en']],
    // 'PL' =>  ['default' => 'pl', 'all' => ['pl', 'en']],
    // 'RO' =>  ['default' => 'ro', 'all' => ['ro', 'en']],
    // 'SE' =>  ['default' => 'sv', 'all' => ['sv', 'en']],
    // 'TW' =>  ['default' => 'zh', 'all' => ['zh', 'en']],

    // 'US' =>  ['default' => 'en', 'all' => ['en']],
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

  static public function defaultLanguage(string $country, ?string $suggestedLanguage = null): string
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
