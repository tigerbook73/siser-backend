<?php

namespace App\Notifications;

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
