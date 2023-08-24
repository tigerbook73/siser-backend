<?php

namespace App\Notifications;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\Subscription;
use Carbon\Carbon;
use NumberFormatter;

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

    return __($key, $replace, $this->locale);
  }

  public function formatDate(Carbon|null $date)
  {
    return $date ? $date->setTimezone($this->timezone)
      ->locale($this->locale)
      ->isoFormat('lll z') : '';
  }

  public function formatOrderType(int $period)
  {
    return ($period > 1) ? 'Renewal' : 'New Subscription';
  }

  public function formatOrderStatus(string $status)
  {
    return $this->trans('messages.order.status.' . $status);
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

  public function formatPriceWithCurrency(string|float $price)
  {
    // TODO: en_US, 'AUD' => 'A$'
    // $fmt = numfmt_create($this->locale, NumberFormatter::CURRENCY);
    // return numfmt_format_currency($fmt, (float)$price, $this->currency);
    return $this->currency . ' ' . number_format((float)$price, 2);
  }

  public function formatPaymentMethodType(string $type)
  {
    return $this->trans('messages.payment_method.' . $type);
  }

  public function formatPaymentMethod(string $type, array|null $display_data)
  {
    if ($type == 'creditCard' || $type == 'googlePay') {
      $text  = '<div>' . $this->trans('messages.credit_card.brand', ['brand' => $display_data['brand']]) . '</div>';
      $text .= '<div>' . $this->trans('messages.credit_card.card_no', ['last_four_digits' => $display_data['last_four_digits']])  . '</div>';
      $text .= '<div>' . $this->trans('messages.credit_card.expire_at', ['month' => $display_data['expiration_month'], 'year' =>  $display_data['expiration_year']])  . '</div>';

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

  public function getTaxName()
  {
    return match ($this->country) {
      'US' => 'Sales Tax',
      'CA' => 'GST/HST',
      'AU' => 'GST',
      default => __('messages.tax_name', [], $this->locale),
    };
  }

  public function showStart(string $type)
  {
    return (!in_array($type, [
      SubscriptionNotification::NOTIF_ORDER_ABORTED,
      SubscriptionNotification::NOTIF_ORDER_CANCELLED,
    ]));
  }

  public function showEnd(string $type)
  {
    return (in_array($type, [
      SubscriptionNotification::NOTIF_CANCELLED,
      SubscriptionNotification::NOTIF_CANCELLED_REFUND,
      SubscriptionNotification::NOTIF_FAILED,
      SubscriptionNotification::NOTIF_TERMINATED,
    ]));
  }

  public function showPeriod(string $type)
  {
    return (in_array($type, [
      SubscriptionNotification::NOTIF_ORDER_CONFIRMED,
      SubscriptionNotification::NOTIF_EXTENDED,
      SubscriptionNotification::NOTIF_ORDER_INVOICE,
      SubscriptionNotification::NOTIF_INVOICE_PENDING,
      SubscriptionNotification::NOTIF_REMINDER,
    ]));
  }

  public function showNextInvoice(string $type)
  {
    return (in_array($type, [
      SubscriptionNotification::NOTIF_ORDER_CONFIRMED,
      SubscriptionNotification::NOTIF_EXTENDED,
      SubscriptionNotification::NOTIF_ORDER_INVOICE,
      SubscriptionNotification::NOTIF_INVOICE_PENDING,
      SubscriptionNotification::NOTIF_REMINDER,
    ]));
  }

  public function formatCouponDescription(array $coupon)
  {
    return $this->trans('messages.coupon.description', [
      'code' => $coupon['code'],
      'description' => $coupon['description'],
      'tax' => $this->getTaxName()
    ]);
  }

  public function formatBillingPeriod(Subscription $subscription)
  {
    return $this->trans('messages.subscription.billing_period.monthly' .
      (($subscription->coupon_info && $subscription->coupon_info['percentage_off'] >= 100) ? '_trial' : ''));
  }

  public function formatPeriod(Subscription $subscription)
  {
    return ($subscription->coupon_info && $subscription->coupon_info['percentage_off'] >= 100) ?
      $this->trans('messages.subscription.period_free_trial') :
      $subscription->current_period;
  }
}
