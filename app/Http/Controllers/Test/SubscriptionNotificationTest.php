<?php

namespace App\Http\Controllers\Test;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\LicensePackage;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodDisplayData;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Locale;
use DateTimeZone;
use Illuminate\Support\Carbon;

class SubscriptionNotificationTest
{

  public ?Country $country = null;
  public ?Plan $plan = null;
  public ?Coupon $coupon = null;
  public ?LicensePackage $licensePackage = null;
  public ?User $user = null;
  public ?BillingInfo $billingInfo = null;
  public ?PaymentMethod $paymentMethod = null;
  public ?Subscription $subscription = null;
  public ?Invoice $invoice = null;
  public ?Refund $refund = null;


  static public function init(string $country, string $plan)
  {
    $inst = new self();
    $inst->updateCountry($country);
    $inst->updatePlan($plan);
    $inst->updateUser();
    $inst->updateBillingInfo();
    $inst->updatePaymentMethod();

    return $inst;
  }

  static public function clean()
  {
    /** @var ?User $user */
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

  public function updateCountry(?string $country = null)
  {
    $this->country = Country::findByCode($country ?? 'US');
    return $this;
  }

  public function updatePlan(string $interval)
  {
    /** @var ?Plan $plan */
    $plan = Plan::public()->where('interval', $interval)->first();

    $this->plan = $plan;
    return $this;
  }

  public function updateUser()
  {
    /** @var ?User $user */
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
    /** @var ?BillingInfo @billingInfo */
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

  public function updatePaymentMethod(?string $type = null)
  {
    /** @var ?PaymentMethod $paymentMethod */
    $paymentMethod = PaymentMethod::where('user_id', $this->user->id)->first();

    $this->paymentMethod =  $paymentMethod ?? new PaymentMethod();
    $this->paymentMethod->user_id       = $this->user->id;
    $this->paymentMethod->type          = $type ?? $this->paymentMethod->type ?? "googlePay";
    $this->paymentMethod->setDisplayData(new PaymentMethodDisplayData(
      brand: 'Visa',
      expiration_year: 2099,
      expiration_month: 12,
      last_four_digits: '1111'
    ));
    $this->paymentMethod->dr = [];
    $this->paymentMethod->save();
    return $this;
  }

  public function createFakeSubscription(
    User $user,
    Plan $plan,
  ) {
    $user->getActiveSubscription()?->stop(Subscription::STATUS_STOPPED, 'test');
    $subscription = (new Subscription())
      ->initFill()
      ->fillBillingInfo($user->billing_info ?? BillingInfo::createDefault($user))
      ->fillPlanAndCoupon($plan);
    $subscription->setStatus(Subscription::STATUS_ACTIVE);
    $subscription->save();
    $subscription->user->updateSubscriptionLevel();

    return $subscription;
  }

  public function updateSubscription(
    ?Carbon $startDate = null,
    ?int $currentPeriod = null,
    ?string $status = null,
    ?string $subStatus = null,
  ) {
    // default value
    $startDate = $startDate ?? now()->subDays(2);

    $this->subscription?->stop(Subscription::STATUS_STOPPED, "Test stop");
    $this->subscription = $this->createFakeSubscription(
      user: $this->user,
      plan: $this->plan
    );

    $this->subscription->setPaymentMethodInfo($this->paymentMethod->info());
    $this->subscription->current_period = $currentPeriod ?? $this->subscription->current_period ?? 0;
    $this->subscription->start_date = $startDate;

    // period start & end date
    if ($this->subscription->isFreeTrial()) {
      if ($this->subscription->current_period <= 1) {
        // free trial
        $this->subscription->current_period_start_date  = $this->subscription->start_date;
        $this->subscription->current_period_end_date    = $this->subscription->start_date->addUnit(
          $this->subscription->getCouponInfo()->interval,
          $this->subscription->getCouponInfo()->interval_count
        );
      } else {
        // free trial
        $this->subscription->current_period_start_date    = $this->subscription->start_date->addUnit(
          $this->subscription->getCouponInfo()->interval,
          $this->subscription->getCouponInfo()->interval_count
        );
        // normal
        $this->subscription->current_period_start_date    = $this->subscription->current_period_start_date->add(
          $this->subscription->getPlanInfo()->interval,
          $this->subscription->getPlanInfo()->interval_count * ($this->subscription->current_period - 2)
        );
        $this->subscription->current_period_end_date    = $this->subscription->start_date->addUnit(
          $this->subscription->getPlanInfo()->interval,
          $this->subscription->getPlanInfo()->interval_count
        );
      }
    } else {
      $this->subscription->current_period_end_date    = $this->subscription->start_date->addUnit(
        $this->subscription->getPlanInfo()->interval,
        $this->subscription->getPlanInfo()->interval_count
      );
      $this->subscription->current_period_start_date      = $this->subscription->current_period_end_date->subUnit(
        $this->subscription->getPlanInfo()->interval,
        $this->subscription->getPlanInfo()->interval_count
      );
    }
    $this->subscription->next_invoice_date            = $this->subscription->current_period_end_date->subDays(1);
    $this->subscription->next_reminder_date           = $this->subscription->current_period_end_date->subDays(8);

    $this->subscription->status                       = $status ?? $this->subscription->status ?? Subscription::STATUS_DRAFT;
    $this->subscription->sub_status                   = $subStatus ?? $this->subscription->sub_status ?? Subscription::SUB_STATUS_NORMAL;
    $this->subscription->save();
    return $this;
  }
}
