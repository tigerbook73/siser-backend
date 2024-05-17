<?php

namespace App\Http\Controllers\Test;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Invoice;
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
  public function __construct(
    public Country|null $country = null,
    public Plan|null $plan = null,
    public Coupon|null $coupon = null,
    public array|null $planInfo = null,
    public User|null $user = null,
    public BillingInfo|null $billingInfo = null,
    public PaymentMethod|null $paymentMethod = null,
    public Subscription|null $subscription = null,
    public Invoice|null $invoice = null,
    public Refund|null $refund = null,
  ) {
  }

  static public function init(string $country, string $plan, string $coupon)
  {
    $inst = new self();
    $inst->updateCountry($country);
    $inst->updatePlan($plan);
    $inst->updateUser();
    $inst->updateBillingInfo();
    $inst->updatePaymentMethod();

    $orderDate = now()->subHour();
    $inst->updateSubscription(startDate: $orderDate);
    $inst->updateInvoice(Invoice::STATUS_PENDING);
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
    $user->subscription_renewals()->delete();
    $user->subscriptions()->delete();
    $user->payment_method()->delete();
    $user->billing_info()->delete();
    $user->lds_license()->delete();
    $user->delete();

    Coupon::where('code', 'like', '%SNT%')->delete();
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
      $interval_count = 20;
      $percentage_off = 100;
      $name = 'LDS Pro 20-day free trial';
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
    $this->coupon->condition      = [
      'new_customer_only' => false,
      'new_subscription_only' => false,
      'upgrade_only' => false
    ];
    $this->coupon->start_date = now();
    $this->coupon->end_date = Carbon::parse('2099-12-31');
    $this->coupon->status = Coupon::STATUS_ACTIVE;
    $this->coupon->save();

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
    $this->paymentMethod->setDrSourceId('dr-source-0000');
    $this->paymentMethod->save();
    return $this;
  }

  public function updateSubscription(Carbon $startDate = null, $currentPeriod = null, $taxRate = null, $status = null, $subStatus = null)
  {
    $this->subscription = $this->subscription ?? new Subscription();
    $this->subscription->fillBillingInfo($this->billingInfo);
    $this->subscription->fillPaymentMethod($this->paymentMethod);
    $this->subscription->fillPlanAndCoupon($this->plan, $this->coupon);
    $this->subscription->subtotal                     = $this->subscription->price;
    $this->subscription->tax_rate                     = $taxRate && $this->subscription->tax_rate ?: 0.1;
    $this->subscription->total_tax                    = $this->subscription->subtotal * $this->subscription->tax_rate;
    $this->subscription->total_amount                 = $this->subscription->subtotal * (1 + $this->subscription->tax_rate);

    $this->subscription->current_period               = $currentPeriod ?? $this->subscription->current_period ?? 0;
    $this->subscription->start_date                   = $startDate ?? $this->subscription->start_date ?? now();

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

  public function updateSubscriptionCoupon()
  {
    $this->subscription->fillPlanAndCoupon($this->plan, $this->coupon);
    $this->subscription->fillNextInvoice();
    $this->subscription->save();
    return $this;
  }

  public function updateInvoice(string $status = null, bool $next = false)
  {
    $this->invoice = $this->invoice ?? new Invoice();

    $this->invoice->fillBasic($this->subscription);
    $this->invoice->fillPeriod($this->subscription, $next);

    if ($next) {
      $this->invoice->subtotal              = $this->subscription->next_invoice['subtotal'];
      $this->invoice->total_tax             = $this->subscription->next_invoice['total_tax'];
      $this->invoice->total_amount          = $this->subscription->next_invoice['total_amount'];
      $this->invoice->total_refunded        = 0;
    } else {
      $this->invoice->subtotal              = $this->subscription->subtotal;
      $this->invoice->total_tax             = $this->subscription->total_tax;
      $this->invoice->total_amount          = $this->subscription->total_amount;
      $this->invoice->total_refunded        = 0;
    }

    $this->invoice->pdf_file              = "/robots.txt";
    $this->invoice->status                = $status ?? $this->invoice->status ?? Invoice::STATUS_PENDING;
    $this->invoice->dr                    = [
      "file_id"   =>  "dr-file-0000",
      "order_id"  =>  "dr-order-0000",
    ];
    $this->invoice->save();
    return $this;
  }

  public function updateInvoiceCoupon()
  {
    // if ($this->subscription->current_period > 1) {
    //   return $this;
    // }

    $this->invoice->$this->invoice->coupon_info      = $this->coupon->info();
    $this->invoice->subtotal         = $this->subscription->subtotal;
    $this->invoice->total_tax        = $this->subscription->total_tax;
    $this->invoice->total_amount     = $this->subscription->total_amount;
    $this->invoice->save();
    return $this;
  }

  public function updateRefund(bool $success = true)
  {
    /** @var Refund|null @refund */
    $refund = Refund::where('dr->refund_id', 'dr-refund-id-0000')->first();

    $this->refund = $refund ??
      Refund::newFromInvoice($this->invoice, $this->invoice->total_amount - $this->invoice->total_refunded, "test reason");
    $this->refund->setDrRefundId('dr-refund-id-0000');
    $this->refund->setStatus($success ? Refund::STATUS_COMPLETED : Refund::STATUS_FAILED);
    $this->refund->save();

    $this->invoice->total_refunded = $success ? $this->invoice->total_amount : 0;
    $this->invoice->save();
  }
}
