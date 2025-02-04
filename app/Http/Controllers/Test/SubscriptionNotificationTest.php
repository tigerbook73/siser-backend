<?php

namespace App\Http\Controllers\Test;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\LicensePackage;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Locale;
use DateTimeZone;
use Illuminate\Support\Carbon;

class SubscriptionNotificationTest
{

  public Country|null $country = null;
  public Plan|null $plan = null;
  public Coupon|null $coupon = null;
  public LicensePackage|null $licensePackage = null;
  public array|null $planInfo = null;
  public User|null $user = null;
  public BillingInfo|null $billingInfo = null;
  public PaymentMethod|null $paymentMethod = null;
  public Subscription|null $subscription = null;
  public Invoice|null $invoice = null;
  public Refund|null $refund = null;


  static public function init(string $country, string $plan)
  {
    $inst = new self();
    $inst->updateCountry($country);
    $inst->updatePlan($plan);
    $inst->updateUser();
    $inst->updateBillingInfo();
    $inst->updatePaymentMethod();
    $inst->updateLicensePackage();

    return $inst;
  }

  static public function clean()
  {
    /** @var User|null $user */
    $user = User::where('name', 'foo.bar')->first();
    if (!$user) {
      return;
    }

    $user->refunds()->delete();
    $user->invoices()->delete();
    $user->subscriptions()->delete();
    $user->payment_method()->delete();
    $user->billing_info()->delete();
    $user->lds_license()->delete();
    $user->delete();

    Coupon::where('coupon_event', 'php-unit')->delete();
  }

  public function updateCountry(string $country = null)
  {
    $this->country = Country::findByCode($country ?? 'US');
    return $this;
  }

  public function updatePlan(string $interval)
  {
    /** @var Plan|null $plan */
    $plan = Plan::public()->where('interval', $interval)->first();

    $this->plan = $plan;
    $this->planInfo = $this->plan->info($this->country->code);
    return $this;
  }

  public function updateCoupon(string $type = null)
  {
    if ($type == 'free-trial') {
      $code = 'free3m-test';
      $discount_type = Coupon::DISCOUNT_TYPE_FREE_TRIAL;
      $interval = Coupon::INTERVAL_DAY;
      $interval_count = 3;
      $percentage_off = 100;
      $name = 'LDS Pro 3-day free trial';
    } else if ($type == 'percentage') {
      $code = '15off0m-test';
      $discount_type = Coupon::DISCOUNT_TYPE_PERCENTAGE;
      $interval = $this->plan->interval;
      $interval_count = 0;
      $percentage_off = 15;
      $name = '15% OFF';
    } else if ($type == 'percentage-fixed-term') {
      $code = '15off3m-test';
      $discount_type = Coupon::DISCOUNT_TYPE_PERCENTAGE;
      $interval = $this->plan->interval;
      $interval_count = $this->plan->interval == Coupon::INTERVAL_MONTH ? 3 : 1;
      $name = "15% OFF for $interval_count $interval";
      $percentage_off = 15;
    } else {
      // ignore
      return $this;
    }

    /** @var Coupon|null $coupon */
    $coupon = Coupon::where('code', $code)->first();
    $this->coupon                 = $coupon ?? new Coupon();
    $this->coupon->code           = $code;
    $this->coupon->coupon_event   = 'php-unit';
    $this->coupon->type           = Coupon::TYPE_SHARED;
    $this->coupon->discount_type  = $discount_type;
    $this->coupon->name           = $name;
    $this->coupon->interval       = $interval;
    $this->coupon->interval_count = $interval_count;
    $this->coupon->percentage_off = $percentage_off;
    $this->coupon->start_date = now();
    $this->coupon->end_date = Carbon::parse('2099-12-31');
    $this->coupon->status = Coupon::STATUS_ACTIVE;
    $this->coupon->save();

    return $this;
  }

  public function updateLicensePackage()
  {
    $name = 'Test License';

    $this->licensePackage = $this->licensePackage ??
      LicensePackage::where('name', $name)->first() ??
      LicensePackage::create([
        'type' => LicensePackage::TYPE_STANDARD,
        'name' => $name,
        'price_table' => [
          ['quantity' => 10, 'discount' => 10],
          ['quantity' => 20, 'discount' => 20],
          ['quantity' => 30, 'discount' => 30],
        ],
        'status' => LicensePackage::STATUS_ACTIVE,
      ]);

    return $this;
  }

  public function updateUser()
  {
    /** @var User|null $user */
    $user = User::where('name', 'foo.bar')->first();

    $this->user = $user ?? new User();
    $this->user->name = "foo.bar";
    $this->user->cognito_id = "11111111-1111-1111-1111-1111111111";
    $this->user->given_name = "Foo";
    $this->user->family_name = "Bar";
    $this->user->full_name = "Foo Bar";
    $this->user->email = "foo.bar@test.com";
    $this->user->phone_number = "+61400000000";
    $this->user->country_code = $this->country->code;
    $this->user->language_code = Locale::defaultLanguage($this->country->code);
    $this->user->timezone = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $this->country->code)[0] ?? 'Australia/Melbourne';
    $this->user->password = "Not allowed!";
    $this->user->save();
    return $this;
  }

  public function updateBillingInfo()
  {
    /** @var BillingInfo|null @billingInfo */
    $billingInfo = BillingInfo::where('user_id', $this->user->id)->first();

    $this->billingInfo = $billingInfo ?? new BillingInfo();
    $this->billingInfo->user_id         = $this->user->id;
    $this->billingInfo->first_name      = $this->user->given_name;
    $this->billingInfo->last_name       = $this->user->family_name;
    $this->billingInfo->phone           = $this->user->phone_number;
    $this->billingInfo->customer_type   = BillingInfo::CUSTOMER_TYPE_INDIVIDUAL;
    $this->billingInfo->organization    = "";
    $this->billingInfo->email           = $this->user->email;
    $this->billingInfo->address         = [
      "line1"       => "123 Test Road",
      "line2"       => "",
      "city"        => "Test City",
      "postcode"    => "30000",
      "state"       => "Test State",
      "country"     => $this->country->code,
    ];
    $this->billingInfo->save();
    return $this;
  }

  public function updatePaymentMethod(string $type = null)
  {
    /** @var PaymentMethod|null $paymentMethod */
    $paymentMethod = PaymentMethod::where('user_id', $this->user->id)->first();

    $this->paymentMethod =  $paymentMethod ?? new PaymentMethod();
    $this->paymentMethod->user_id       = $this->user->id;
    $this->paymentMethod->type          = $type ?? $this->paymentMethod->type ?? "googlePay";
    $this->paymentMethod->display_data  = [
      'brand'               => 'Visa',
      'expiration_year'     => 2099,
      'expiration_month'    => 12,
      'last_four_digits'    => '1111'
    ];
    $this->paymentMethod->dr = [];
    $this->paymentMethod->save();
    return $this;
  }

  public function createFakeSubscription(
    User $user,
    Plan $plan,
    ?Coupon $coupon,
    ?LicensePackage $licensePackage,
    int $licenseQuantity
  ) {
    $user->getActiveSubscription()?->stop(Subscription::STATUS_STOPPED, 'test');
    $subscription = (new Subscription())
      ->initFill()
      ->fillBillingInfo($user->billing_info ?? BillingInfo::createDefault($user))
      ->fillPlanAndCoupon($plan, $coupon, $licensePackage, 2);
    $subscription->setStatus(Subscription::STATUS_ACTIVE);
    $subscription->save();
    $subscription->user->updateSubscriptionLevel();

    return $subscription;
  }

  public function updateSubscription(
    Carbon $startDate = null,
    int|null $currentPeriod = null,
    string|null $status = null,
    string|null $subStatus = null,
    int $licenseCount = 0
  ) {
    // default value
    $startDate = $startDate ?? now()->subDays(2);

    $this->subscription?->stop(Subscription::STATUS_STOPPED, "Test stop");
    $this->subscription = $this->createFakeSubscription(
      user: $this->user,
      plan: $this->plan,
      coupon: $this->coupon,
      licensePackage: $licenseCount ? $this->licensePackage : null,
      licenseQuantity: $licenseCount,
    );

    $this->subscription->fillPaymentMethod($this->paymentMethod);
    $this->subscription->current_period = $currentPeriod ?? $this->subscription->current_period ?? 0;
    $this->subscription->start_date = $startDate;

    // period start & end date
    if ($this->subscription->isFreeTrial()) {
      if ($this->subscription->current_period <= 1) {
        // free trial
        $this->subscription->current_period_start_date  = $this->subscription->start_date;
        $this->subscription->current_period_end_date    = $this->subscription->start_date->addUnit(
          $this->subscription->coupon_info['interval'],
          $this->subscription->coupon_info['interval_count']
        );
      } else {
        // free trial
        $this->subscription->current_period_start_date    = $this->subscription->start_date->addUnit(
          $this->subscription->coupon_info['interval'],
          $this->subscription->coupon_info['interval_count']
        );
        // normal
        $this->subscription->current_period_start_date    = $this->subscription->current_period_start_date->add(
          $this->subscription->plan_info['interval'],
          $this->subscription->plan_info['interval_count'] * ($this->subscription->current_period - 2)
        );
        $this->subscription->current_period_end_date    = $this->subscription->start_date->addUnit(
          $this->subscription->plan_info['interval'],
          $this->subscription->plan_info['interval_count']
        );
      }
    } else {
      $this->subscription->current_period_end_date    = $this->subscription->start_date->addUnit(
        $this->subscription->plan_info['interval'],
        $this->subscription->plan_info['interval_count']
      );
      $this->subscription->current_period_start_date      = $this->subscription->current_period_end_date->subUnit(
        $this->subscription->plan_info['interval'],
        $this->subscription->plan_info['interval_count']
      );
    }
    $this->subscription->next_invoice_date            = $this->subscription->current_period_end_date->subDays(1);
    $this->subscription->next_reminder_date           = $this->subscription->current_period_end_date->subDays(8);

    $this->subscription->fillNextInvoice();
    $this->subscription->status                       = $status ?? $this->subscription->status ?? Subscription::STATUS_DRAFT;
    $this->subscription->sub_status                   = $subStatus ?? $this->subscription->sub_status ?? Subscription::SUB_STATUS_NORMAL;
    $this->subscription->save();
    return $this;
  }
}
