<?php

namespace App\Models;

use App\Models\Base\Subscription as BaseSubscription;
use App\Notifications\SubscriptionNotification;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;

class Subscription extends BaseSubscription
{
  use Notifiable;

  static protected $attributesOption = [
    'id'                        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'                   => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_id'                   => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_id'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'billing_info'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_info'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_info'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'processing_fee_info'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'price'                     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'processing_fee'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subtotal'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_tax'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_amount'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'start_date'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'end_date'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_level'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'current_period'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'current_period_start_date' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'current_period_end_date'   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'next_invoice_date'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'stop_reason'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'sub_status'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'created_at'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  static protected $withAttrs = ['plan', 'coupon'];

  static public function createBasicMachineSubscription(User $user): Subscription
  {
    /** @var Plan $plan */
    $plan = Plan::find(config('siser.plan.default_machine_plan'));

    return Subscription::create([
      'user_id'                   => $user->id,
      'plan_id'                   => $plan->id,
      'coupon_id'                 => null,
      'billing_info'              => ($user->billing_info ?? BillingInfo::createDefault($user))->toResource('customer'),
      'plan_info'                 => $plan->toPublicPlan('US'),
      'coupon_info'               => null,
      'processing_fee_info'       => [
        'explicit_processing_fee' => false,
        'processing_fee_rate'     => 0,
      ],
      'currency'                  => 'USD',
      'price'                     => 0.0,
      'processing_fee'            => 0.0,
      'subtotal'                  => 0.0,
      'total_tax'                 => 0.0,
      'total_amount'              => 0.0,
      'subscription_level'        => 1,
      'current_period'            => 0,
      'start_date'                => new Carbon(),
      'end_date'                  => null,
      'current_period_start_date' => null,
      'current_period_end_date'   => null,
      'next_invoice_date'         => null,
      'status'                    => 'active',
      'sub_status'                => 'normal',
    ]);
  }

  public function stop(string $status, string $stopReason = '', string $subStatus = 'normal')
  {
    $this->status = $status;
    $this->stop_reason = $stopReason;
    $this->sub_status = $subStatus;

    $this->end_date = $this->start_date ? now() : null;
    $this->next_invoice_date = null;
    $this->save();
  }

  // public function activate($start_date = null, )
  // {
  //   $this->status = 'active';
  //   $this->start_date = now();

  //   $this->end_date = $this->start_date ? now() : null;
  //   $this->next_invoice_date = null;
  //   $this->save();
  // }

  public function routeNotificationForMail($notification)
  {
    return [
      $this->billing_info['email'] => $this->billing_info['first_name'] . ' ' . $this->billing_info['last_name']
    ];
  }

  public function sendNotification(string $type, Invoice|null $invoice = null)
  {
    $this->notify(new SubscriptionNotification($type, $this, $invoice));
  }
}
