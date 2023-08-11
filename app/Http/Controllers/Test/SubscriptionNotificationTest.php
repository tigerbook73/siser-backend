<?php

namespace App\Http\Controllers\Test;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Locale;
use Illuminate\Support\Carbon;

class SubscriptionNotificationTest
{
  public const CouponCode = 'SNT-A1B2C3D4';

  public function __construct(
    public Country|null $country = null,
    public Plan|null $plan = null,
    public Coupon|null $coupon = null,
    public array|null $publicPlan = null,
    public User|null $user = null,
    public BillingInfo|null $billingInfo = null,
    public PaymentMethod|null $paymentMethod = null,
    public Subscription|null $subscription = null,
    public Invoice|null $invoice = null,
  ) {
  }

  static public function init(string $country = 'US')
  {
    $inst = new self();
    $inst->updateCountry($country);
    $inst->updatePlan();
    $inst->updateUser();
    $inst->updateBillingInfo();
    $inst->updatePaymentMethod();

    $orderDate = now()->subHour();
    $inst->updateSubscription(startDate: $orderDate);
    $inst->updateInvoice(invoiceDate: $orderDate, status: Invoice::STATUS_PENDING);
    return $inst;
  }

  static public function clean()
  {
    /** @var User|null $user */
    $user = User::where('name', 'foo.bar')->first();
    if (!$user) {
      return;
    }

    $user->invoices()->delete();
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

  public function updatePlan()
  {
    /** @var Plan|null $plan */
    $plan = Plan::public()->first();

    $this->plan = $plan;
    $this->publicPlan = $this->plan->toPublicPlan($this->country->code);
    return $this;
  }

  public function updateCoupon(string $code = null)
  {
    /** @var Coupon|null $coupon */
    $coupon = Coupon::where('code', self::CouponCode)->first();
    $this->coupon =  $coupon ?? new Coupon();
    $this->coupon->code = self::CouponCode;
    $this->coupon->description = ($code == 'percentage-off') ? '10% off' : 'First 1 month free, then full price';
    $this->coupon->condition = [
      'new_customer_only' => false,
      'new_subscription_only' => false,
      'upgrade_only' => false
    ];
    $this->coupon->period = 0;
    $this->coupon->percentage_off = ($code == 'percentage-off') ? 10 : 100;
    $this->coupon->start_date = now();
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
    $this->user->timezone = "Australia/Sydney";
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
    $this->billingInfo->tax_id          = null;
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
      "brand"               => "Visa",
      "expiration_year"     => 2099,
      "expiration_month"    => 12,
      "last_four_digits"    => "1111"
    ];
    $this->paymentMethod->dr            = [
      "source_id" => "dr-source-0000"
    ];
    $this->paymentMethod->save();
    return $this;
  }

  public function updateSubscription(Carbon $startDate = null, $currentPeriod = null, $taxRate = null, $status = null, $subStatus = null)
  {
    $this->subscription = $this->subscription ?? new Subscription();
    $this->subscription->user_id                      = $this->user->id;
    $this->subscription->plan_id                      = $this->plan->id;
    $this->subscription->coupon_id                    = null;
    $this->subscription->coupon_info                  = null;
    $this->subscription->billing_info                 = $this->billingInfo->toResource("customer");
    $this->subscription->plan_info                    = $this->plan->toPublicPlan($this->billingInfo->address["country"]);
    $this->subscription->currency                     = $this->country->currency;
    $this->subscription->price                        = $this->publicPlan['price']['price'];
    $this->subscription->subtotal                     = $this->publicPlan['price']['price'];
    $this->subscription->tax_rate                     = $taxRate && $this->subscription->tax_rate ?: 0.1;
    $this->subscription->total_tax                    = $this->subscription->subtotal * $this->subscription->tax_rate;
    $this->subscription->total_amount                 = $this->subscription->subtotal * (1 + $this->subscription->tax_rate);
    $this->subscription->subscription_level           = $this->plan->subscription_level;
    $this->subscription->current_period               = $currentPeriod ?? $this->subscription->current_period ?? 0;
    $this->subscription->start_date                   = $startDate ?? $this->subscription->start_date ?? now();
    $this->subscription->end_date                     = $this->subscription->start_date->addMonths(10);
    $this->subscription->current_period_start_date    = $this->subscription->start_date->addMonths(($this->subscription->current_period ?: 1) - 1);
    $this->subscription->current_period_end_date      = $this->subscription->start_date->addMonths(($this->subscription->current_period ?: 1));
    $this->subscription->next_invoice_date            = $this->subscription->start_date->addMonths(($this->subscription->current_period ?: 1))->subDays(5);
    $this->subscription->next_invoice                 = [
      "price"                       => $this->subscription->price,
      "subtotal"                    => $this->subscription->subtotal,
      "tax_rate"                    => $this->subscription->tax_rate,
      "plan_info"                   => $this->subscription->plan_info,
      "total_tax"                   => $this->subscription->total_tax,
      "coupon_info"                 => $this->subscription->coupon_info,
      "total_amount"                => $this->subscription->total_amount,
      "current_period_start_date"   => $this->subscription->current_period_start_date->addMonths(1),
      "current_period_end_date"     => $this->subscription->current_period_end_date->addMonths(1),
    ];
    $this->subscription->status                       = $status ?? $this->subscription->status ?? Subscription::STATUS_PENDING;
    $this->subscription->sub_status                   = $subStatus ?? $this->subscription->sub_status ?? Subscription::SUB_STATUS_NORMAL;

    $this->subscription->save();
    return $this;
  }

  public function updateSubscriptionCoupon()
  {
    if ($this->subscription->current_period > 1) {
      return $this;
    }

    $this->subscription->coupon_id        = $this->coupon->id;
    $this->subscription->coupon_info      = $this->coupon->info();
    $this->subscription->subtotal         = $this->subscription->price * (100 - $this->coupon->percentage_off) / 100;
    $this->subscription->total_tax        = $this->subscription->subtotal * $this->subscription->tax_rate;
    $this->subscription->total_amount     = $this->subscription->subtotal * (1 + $this->subscription->tax_rate);

    $next_invoice = $this->subscription->next_invoice;
    if ($this->subscription->coupon_info['percentage_off'] >= 100) {
      $next_invoice['coupon_info']        = null;
      $next_invoice["subtotal"]           = $this->subscription->price;
      $next_invoice["total_tax"]          = $next_invoice["subtotal"] * $this->subscription->tax_rate;
      $next_invoice["total_amount"]       = $next_invoice["subtotal"] * (1 + $this->subscription->tax_rate);
    } else {
      $next_invoice['coupon_info']        = $this->subscription->coupon_info;
      $next_invoice["subtotal"]           = $this->subscription->subtotal;
      $next_invoice["total_tax"]          = $this->subscription->total_tax;
      $next_invoice["total_amount"]       = $this->subscription->total_amount;
    }
    $this->subscription->next_invoice = $next_invoice;

    $this->subscription->save();
    return $this;
  }

  public function updateInvoice(int $period = null, Carbon $invoiceDate = null, string $status = null)
  {
    $this->invoice = $this->invoice ?? new Invoice();

    $this->invoice->user_id               =  $this->user->id;
    $this->invoice->subscription_id       =  $this->subscription->id;
    $this->invoice->period                = $period ?? $this->invoice->period ?? $this->subscription->current_period;
    $this->invoice->period_start_date     = $this->subscription->current_period_start_date;
    $this->invoice->period_end_date       = $this->subscription->current_period_end_date;
    $this->invoice->currency              = $this->subscription->currency;
    $this->invoice->plan_info             = $this->subscription->plan_info;
    $this->invoice->coupon_info           = $this->subscription->coupon_info;
    $this->invoice->payment_method_info   =  $this->user->payment_method->info();
    $this->invoice->subtotal              =  $this->subscription->subtotal;
    $this->invoice->total_tax             =  $this->subscription->total_tax;
    $this->invoice->total_amount          =  $this->subscription->total_amount;
    $this->invoice->invoice_date          = $invoiceDate ?? $this->invoice->invoice_date ?? $this->subscription->next_invoice_date;
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
    if ($this->subscription->current_period > 1) {
      return $this;
    }

    $this->invoice->coupon_info      = $this->coupon->info();
    $this->invoice->subtotal         = $this->subscription->subtotal;
    $this->invoice->total_tax        = $this->subscription->total_tax;
    $this->invoice->total_amount     = $this->subscription->total_amount;
    $this->invoice->save();
    return $this;
  }
}
