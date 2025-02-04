<?php

namespace App\Notifications;

use App\Models\Country;
use App\Models\Invoice;
use App\Models\Subscription;
use Carbon\Carbon;

class EmailHelper
{
  public string $language = "";

  public function __construct(public string $locale, public string $timezone, public string $country, public string $currency)
  {
    $this->language = explode('_', $locale)[0] ?? 'en';
  }

  public function trans(string $key, array $replace = [])
  {
    // auto append: tax name (more can be added)
    if (!isset($replace['tax'])) {
      $replace['tax'] = $this->getTaxName();
    }

    return __('messages.' . $key, $replace, $this->locale);
  }

  public function transWithoutAppend(string $key, array $replace = [])
  {
    return __('messages.' . $key, $replace, $this->locale);
  }

  public function formatDate(Carbon|string|null $date)
  {
    return $date ? (Carbon::parse($date))->setTimezone($this->timezone)
      ->locale($this->locale)
      ->isoFormat('lll z') : '';
  }

  public function formatName(array $billing_info)
  {
    return $billing_info['first_name'] . ' ' . $billing_info['last_name'];
  }

  public function formatCountry(string $code)
  {
    return Country::findByCode($code)->name;
  }

  public function formatAddress(array $address)
  {
    $formattedAddress = '<div>' . $address['line1'] . '</div>';
    if (isset($address['line2'])) {
      $formattedAddress .= '<div>' . $address['line2'] . '</div>';
    }
    $formattedAddress .= '<div>' . $address['city'] . '</div>';
    $formattedAddress .= '<div>' . $address['state'] . ' ' . $address['postcode'] . '</div>';
    $formattedAddress .= '<div>' . $this->formatCountry($address['country']) . '</div>';

    return $formattedAddress;
  }

  public function formatPrice(string|float $price)
  {
    // TODO: en_US, 'AUD' => 'A$'
    // $fmt = numfmt_create($this->locale, NumberFormatter::CURRENCY);
    // return numfmt_format_currency($fmt, (float)$price, $this->currency);
    return number_format((float)$price, 2);
  }

  public function formatPaymentMethod(string $type, array|null $display_data)
  {
    if ($type == 'creditCard' || $type == 'googlePay') {
      $text  = '<div>' . $this->trans('credit_card.brand', ['brand' => $display_data['brand']]) . '</div>';
      $text .= '<div>' . $this->trans('credit_card.card_no', ['last_four_digits' => $display_data['last_four_digits']])  . '</div>';
      $text .= '<div>' . $this->trans('credit_card.expire_at', ['month' => $display_data['expiration_month'], 'year' =>  $display_data['expiration_year']])  . '</div>';
      return $text;
    }
    return '';
  }

  public function getCustomerPortalLink()
  {
    return '<a href="' . config('app.url') . '/account/subscription" target="_blank">Customer Portal</a>';
  }

  public function getSupportLink()
  {
    return '<a href="' . config('app.url') . '/support" target="_blank">FAQs</a>';
  }

  public function getSupportEmailLink()
  {
    return '<a href="mailto:' . config("siser.support_email") . '">' . config("siser.support_email") . '</a>';
  }

  public function getCustomerSupportLink()
  {
    return '<a href="https://siser.freshdesk.com/en/support/home" target="_blank">Customer Support</a>';
  }

  public function getTermsLink()
  {
    return '<a href="https://fcl.software/legal/eula" target="_blank">terms and conditions</a>';
  }

  public function getRenewLink()
  {
    return '<a href="https://software.siser.com/account/subscription" target="_blank">here</a>';
  }

  public function getTaxName()
  {
    return match ($this->country) {
      'US' => 'Sales Tax',
      'CA' => 'GST/HST',
      'AU' => 'GST',
      default => __('messages.tax_name', [], $this->locale),
    };
  }
}
