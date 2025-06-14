<?php

namespace App\Models;

use App\Models\Base\TaxId as BaseTaxId;


class TaxId extends BaseTaxId
{
  public const STATUS_PENDING       = 'pending';
  public const STATUS_VERIFIED      = 'verified';
  public const STATUS_INVALID       = 'not_valid';

  public const CUSTOMER_TYPE_INDIVIDUAL = 'individual';
  public const CUSTOMER_TYPE_BUSINESS   = 'business';
  public const CUSTOMER_TYPE_INDIVIDUAL_BUSINESS = 'individual_business';

  static public $drTaxIds =  [
    [
      'country' => 'AU',
      'country_name' => 'Australia',
      'type' => 'au',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'ABN',
    ],
    [
      'country' => 'AT',
      'country_name' => 'Austria',
      'type' => 'at',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'BH',
      'country_name' => 'Bahrain',
      'type' => 'bh',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'BY',
      'country_name' => 'Belarus',
      'type' => 'by',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'BE',
      'country_name' => 'Belgium',
      'type' => 'be',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'BR',
      'country_name' => 'Brazil',
      'type' => 'br',
      'customer_type' => 'business',
      'entity' => 'DR_BRAZIL-ENTITY',
      'required' => true,
      'description' => 'Cadastro Nacional da Pessoa Jurídica',
    ],
    [
      'country' => 'BR',
      'country_name' => 'Brazil',
      'type' => 'br_ie',
      'customer_type' => 'business',
      'entity' => 'DR_BRAZIL-ENTITY',
      'required' => true,
      'description' => 'Inscrição Estadual',
    ],
    [
      'country' => 'BR',
      'country_name' => 'Brazil',
      'type' => 'br_natural',
      'customer_type' => 'individual',
      'entity' => 'DR_BRAZIL-ENTITY',
      'required' => true,
      'description' => 'Cadastro de Pessoas Físicas (individuals)',
    ],
    [
      'country' => 'BG',
      'country_name' => 'Bulgaria',
      'type' => 'bg',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'CA',
      'country_name' => 'Canada',
      'type' => 'ca',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'Canadian GST',
    ],
    [
      'country' => 'CA',
      'country_name' => 'Canada',
      'type' => 'ca',
      'customer_type' => 'business',
      'entity' => 'C5_INC-ENTITY',
      'required' => false,
      'description' => 'GST Number',
    ],
    [
      'country' => 'CL',
      'country_name' => 'Chile',
      'type' => 'cl',
      'customer_type' => 'individual',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'RUT',
    ],
    [
      'country' => 'CL',
      'country_name' => 'Chile',
      'type' => 'cl',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'RUT',
    ],
    [
      'country' => 'CO',
      'country_name' => 'Colombia',
      'type' => 'co',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'HR',
      'country_name' => 'Croatia',
      'type' => 'hr',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'CY',
      'country_name' => 'Cyprus',
      'type' => 'cy',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'CZ',
      'country_name' => 'Czech Republic',
      'type' => 'cz',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'DK',
      'country_name' => 'Denmark',
      'type' => 'dk',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'EE',
      'country_name' => 'Estonia',
      'type' => 'ee',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'FI',
      'country_name' => 'Finland',
      'type' => 'fi',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'FR',
      'country_name' => 'France',
      'type' => 'fr',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'DE',
      'country_name' => 'Germany',
      'type' => 'de',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'GR',
      'country_name' => 'Greece',
      'type' => 'gr',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'HU',
      'country_name' => 'Hungary',
      'type' => 'hu',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'IS',
      'country_name' => 'Iceland',
      'type' => 'is',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'IN',
      'country_name' => 'India',
      'type' => 'in',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'GSTIN',
    ],
    [
      'country' => 'ID',
      'country_name' => 'Indonesia',
      'type' => 'id',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'NPWP',
    ],
    [
      'country' => 'IE',
      'country_name' => 'Ireland',
      'type' => 'ie',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'IM',
      'country_name' => 'Isle of Man',
      'type' => 'uk',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'IM',
      'country_name' => 'Isle of Man',
      'type' => 'uk',
      'customer_type' => 'business',
      'entity' => 'DR_UK-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'IT',
      'country_name' => 'Italy',
      'type' => 'it',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'IT',
      'country_name' => 'Italy',
      'type' => 'it_cf',
      'customer_type' => 'individual',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'Codice Fiscal (individuals)',
    ],
    [
      'country' => 'IT',
      'country_name' => 'Italy',
      'type' => 'it_natural',
      'customer_type' => 'individual',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number (individuals)',
    ],
    [
      'country' => 'JP',
      'country_name' => 'Japan',
      'type' => 'jp',
      'customer_type' => 'business',
      'entity' => 'DR_JAPAN-ENTITY',
      'required' => false,
      'description' => 'TIN',
    ],
    [
      'country' => 'JP',
      'country_name' => 'Japan',
      'type' => 'jp_offshore',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'Consumption Tax ID (offshore)',
    ],
    [
      'country' => 'KE',
      'country_name' => 'Kenya',
      'type' => 'ke',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => true,
      'description' => 'PIN',
    ],
    [
      'country' => 'KR',
      'country_name' => 'Korea',
      'type' => 'kr_offshore',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number (offshore)',
    ],
    [
      'country' => 'LV',
      'country_name' => 'Latvia',
      'type' => 'lv',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'LT',
      'country_name' => 'Lithuania',
      'type' => 'lt',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'LU',
      'country_name' => 'Luxembourg',
      'type' => 'lu',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'MY',
      'country_name' => 'Malaysia',
      'type' => 'my',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'MT',
      'country_name' => 'Malta',
      'type' => 'mt',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'MX',
      'country_name' => 'Mexico',
      'type' => 'mx_natural',
      'customer_type' => 'individual',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'RFC',
    ],
    [
      'country' => 'MX',
      'country_name' => 'Mexico',
      'type' => 'mx',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'RFC',
    ],
    [
      'country' => 'MC',
      'country_name' => 'Monaco',
      'type' => 'fr',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'NL',
      'country_name' => 'Netherlands',
      'type' => 'nl',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'NZ',
      'country_name' => 'New Zealand',
      'type' => 'nz',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'GST ID',
    ],
    [
      'country' => 'NO',
      'country_name' => 'Norway',
      'type' => 'no',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'PH',
      'country_name' => 'Philippines',
      'type' => 'ph',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'TIN',
    ],
    [
      'country' => 'PL',
      'country_name' => 'Poland',
      'type' => 'pl',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'PT',
      'country_name' => 'Portugal',
      'type' => 'pt',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'PT',
      'country_name' => 'Portugal',
      'type' => 'pt_nif',
      'customer_type' => 'individual',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'NIF',
    ],
    [
      'country' => 'RO',
      'country_name' => 'Romania',
      'type' => 'ro',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'RU',
      'country_name' => 'Russia',
      'type' => 'ru',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'INN',
    ],
    [
      'country' => 'RU',
      'country_name' => 'Russia',
      'type' => 'ru_natural',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'INN',
    ],
    [
      'country' => 'SA',
      'country_name' => 'Saudi Arabia',
      'type' => 'sa',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'SG',
      'country_name' => 'Singapore',
      'type' => 'sg',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'SK',
      'country_name' => 'Slovakia',
      'type' => 'sk',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'SI',
      'country_name' => 'Slovenia',
      'type' => 'si',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'ZA',
      'country_name' => 'South Africa',
      'type' => 'za',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'ES',
      'country_name' => 'Spain',
      'type' => 'es',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'SE',
      'country_name' => 'Sweden',
      'type' => 'se',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'CH',
      'country_name' => 'Switzerland',
      'type' => 'ch',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'TW',
      'country_name' => 'Taiwan',
      'type' => 'tw',
      'customer_type' => 'business',
      'entity' => 'DR_TAIWAN-ENTITY',
      'required' => true,
      'description' => 'Unified Business Number',
    ],
    [
      'country' => 'TW',
      'country_name' => 'Taiwan',
      'type' => 'tw_offshore',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => true,
      'description' => 'Unified Business Number (offshore)',
    ],
    [
      'country' => 'TH',
      'country_name' => 'Thailand',
      'type' => 'th',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'TR',
      'country_name' => 'Turkey',
      'type' => 'tr',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'UA',
      'country_name' => 'Ukraine',
      'type' => 'ua',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'AE',
      'country_name' => 'United Arab Emirates',
      'type' => 'ae',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'GB',
      'country_name' => 'United Kingdom',
      'type' => 'uk',
      'customer_type' => 'business',
      'entity' => 'DR_IRELAND-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
    [
      'country' => 'GB',
      'country_name' => 'United Kingdom',
      'type' => 'uk',
      'customer_type' => 'business',
      'entity' => 'DR_UK-ENTITY',
      'required' => false,
      'description' => 'VAT Number',
    ],
  ];

  static protected $attributesOption = [
    'id'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr_tax_id'           => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'country'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'customer_type'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'type'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'value'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
  ];

  static function getTaxIdDef(string $country, string $customerType, string $entity)
  {
    foreach (self::$drTaxIds as $taxIdDef) {
      if (
        $taxIdDef['country'] == $country &&
        $taxIdDef['customer_type'] == $customerType &&
        $taxIdDef['entity'] == $entity
      ) {
        return $taxIdDef;
      }
    }
    return null;
  }

  public function info()
  {
    return [
      'id'            => $this->id,
      'dr_tax_id'     => $this->dr_tax_id,
      'customer_type' => $this->customer_type,
      'type'          => $this->type,
      'value'         => $this->value,
    ];
  }
}
