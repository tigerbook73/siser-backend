<?php

namespace App\Models;

use Illuminate\Support\Carbon;

class SubscriptionNextInvoice
{
  /**
   * @param InvoiceItem[] $items
   */
  public function __construct(
    public int $current_period,
    public Carbon $current_period_start_date,
    public Carbon $current_period_end_date,
    public PlanInfo $plan_info,
    public ?CouponInfo $coupon_info,
    public ?LicensePackageInfo $license_package_info,
    public array $items,
    public float $price,
    public float $subtotal,
    public float $discount,
    public float $tax_rate,
    public float $total_tax,
    public float $total_amount,
    public float $credit,
    public float $grand_total,
    public float $credit_to_balance,
  ) {}

  static public function from(array $data): SubscriptionNextInvoice
  {
    return new self(
      current_period: $data['current_period'],
      current_period_start_date: Carbon::parse($data['current_period_start_date']),
      current_period_end_date: Carbon::parse($data['current_period_end_date']),
      plan_info: PlanInfo::from($data['plan_info']),
      coupon_info: isset($data['coupon_info']) ? CouponInfo::from($data['coupon_info']) : null,
      license_package_info: isset($data['license_package_info']) ? LicensePackageInfo::from($data['license_package_info']) : null,
      items: array_map(fn($item) => InvoiceItem::from($item), $data['items']),
      price: (float)$data['price'],
      subtotal: (float)$data['subtotal'],
      discount: (float)$data['discount'],
      tax_rate: (float)$data['tax_rate'],
      total_tax: (float)$data['total_tax'],
      total_amount: (float)$data['total_amount'],
      credit: (float)$data['credit'],
      grand_total: (float)$data['grand_total'],
      credit_to_balance: (float)$data['credit_to_balance'],
    );
  }

  public function toArray(): array
  {
    return [
      'current_period' => $this->current_period,
      'current_period_start_date' => $this->current_period_start_date->format('Y-m-d\TH:i:s\Z'),
      'current_period_end_date' => $this->current_period_end_date->format('Y-m-d\TH:i:s\Z'),
      'plan_info' => $this->plan_info->toArray(),
      'coupon_info' => $this->coupon_info?->toArray(),
      'license_package_info' => $this->license_package_info?->toArray(),
      'items' => array_map(fn($item) => $item->toArray(), $this->items),
      'price' => $this->price,
      'subtotal' => $this->subtotal,
      'discount' => $this->discount,
      'tax_rate' => $this->tax_rate,
      'total_tax' => $this->total_tax,
      'total_amount' => $this->total_amount,
      'credit' => $this->credit,
      'grand_total' => $this->grand_total,
      'credit_to_balance' => $this->credit_to_balance,
    ];
  }
}
