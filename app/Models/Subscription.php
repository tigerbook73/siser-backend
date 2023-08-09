<?php

namespace App\Models;

use App\Models\Base\Subscription as BaseSubscription;
use App\Notifications\SubscriptionNotification;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;

class Subscription extends BaseSubscription
{
  use Notifiable;

  // status
  public const STATUS_ACTIVE                  = 'active';
  public const STATUS_DRAFT                   = 'draft';
  public const STATUS_FAILED                  = 'failed';
  public const STATUS_PENDING                 = 'pending';
  public const STATUS_PROCESSING              = 'processing';
  public const STATUS_STOPPED                 = 'stopped';

  // sub_status (when status is 'active')
  public const SUB_STATUS_CANCELLING          = 'cancelling';
  public const SUB_STATUS_INVOICE_COMPLETING  = 'invoice-completing';
  public const SUB_STATUS_INVOICE_OPEN        = 'invoice-open';
  public const SUB_STATUS_INVOICE_PENDING     = 'invoice-pending';
  public const SUB_STATUS_NORMAL              = 'normal';
  public const SUB_STATUS_ORDER_PENDING       = 'order_pending'; // for STATUS_PENDING

  // dr attributes
  public const DR_CHECKOUT_ID       = 'checkout_id';
  public const DR_ORDER_ID          = 'order_id';
  public const DR_SESSION_ID        = 'checkout_payment_session_id';
  public const DR_SOURCE_ID         = 'source_id';
  public const DR_SUBSCRIPTION_ID   = 'subscription_id';

  static protected $attributesOption = [
    'id'                        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'                   => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_id'                   => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_id'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'billing_info'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'tax_id_info'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_info'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_info'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'price'                     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subtotal'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'tax_rate'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_tax'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_amount'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'start_date'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'end_date'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_level'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'current_period'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'current_period_start_date' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'current_period_end_date'   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'next_invoice_date'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'next_invoice'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'stop_reason'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status_transitions'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'sub_status'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'created_at'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  static protected $withAttrs = ['plan', 'coupon'];

  static public function createBasicMachineSubscription(User $user): Subscription
  {
    /** @var Plan $plan */
    $plan = Plan::find(config('siser.plan.default_machine_plan'));

    $subscription = new Subscription(
      [
        'user_id'                   => $user->id,
        'plan_id'                   => $plan->id,
        'coupon_id'                 => null,
        'billing_info'              => ($user->billing_info ?? BillingInfo::createDefault($user))->toResource('customer'),
        'plan_info'                 => $plan->toPublicPlan('US'),
        'coupon_info'               => null,
        'currency'                  => 'USD',
        'price'                     => 0.0,
        'subtotal'                  => 0.0,
        'tax_rate'                  => 0.0,
        'total_tax'                 => 0.0,
        'total_amount'              => 0.0,
        'subscription_level'        => 1,
        'current_period'            => 0,
        'start_date'                => new Carbon(),
        'end_date'                  => null,
        'current_period_start_date' => null,
        'current_period_end_date'   => null,
        'next_invoice_date'         => null,
        'next_invoice'              => null,
        'status'                    => Subscription::STATUS_ACTIVE,
        'sub_status'                => Subscription::SUB_STATUS_NORMAL,
      ]
    );
    $subscription->setStatus(Subscription::STATUS_DRAFT);
    $subscription->setStatus(Subscription::STATUS_ACTIVE);
    $subscription->save();
    return $subscription;
  }

  public function setStatus(string $status)
  {
    $this->status = $status;

    $status_transitions = $this->status_transitions ?? [];
    $status_transitions[$status] = now();
    $this->status_transitions = $status_transitions;

    return $this;
  }

  public function fillNextInvoice()
  {
    if ((config('dr.dr_mode') != 'prod')) {
      $current_period_start_date = $this->current_period_start_date->addDays(config('dr.dr_test.interval_count'))->toDateTimeString();
      $current_period_end_date = $this->current_period_end_date->addDays(config('dr.dr_test.interval_count'))->toDateTimeString();
    } else {
      $current_period_start_date = $this->current_period_start_date->addMonth()->toDateTimeString();
      $current_period_end_date = $this->current_period_end_date->addMonth()->toDateTimeString();
    }
    $this->next_invoice = [
      "plan_info" =>  $this->plan_info,
      "coupon_info" => $this->coupon_info,
      "price" => $this->price,
      "subtotal" => $this->subtotal,
      "tax_rate" => $this->tax_rate,
      "total_tax" => $this->total_tax,
      "total_amount" => $this->total_amount,
      "current_period_start_date" => $current_period_start_date,
      "current_period_end_date" => $current_period_end_date,
    ];
  }

  public function getDrAttr(string $attr): string|null
  {
    return $this->dr[$attr] ?? null;
  }

  public function getDrCheckoutId()
  {
    return $this->getDrAttr(self::DR_CHECKOUT_ID);
  }

  public function getDrOrderId()
  {
    return $this->getDrAttr(self::DR_ORDER_ID);
  }

  public function getDrSessionId()
  {
    return $this->getDrAttr(self::DR_SESSION_ID);
  }

  public function getDrSourceId()
  {
    return $this->getDrAttr(self::DR_SOURCE_ID);
  }

  public function getDrSubscriptionId()
  {
    return $this->getDrAttr(self::DR_SUBSCRIPTION_ID);
  }

  public function setDrAttr(string $attr, string $value)
  {
    $dr = $this->dr ?? [];
    $dr[$attr] = $value;
    $this->dr = $dr;
    return $this;
  }

  public function setDrCheckoutId(string $checkout_id)
  {
    return $this->setDrAttr(self::DR_CHECKOUT_ID, $checkout_id);
  }

  public function setDrOrderId(string $order_id)
  {
    return $this->setDrAttr(self::DR_ORDER_ID, $order_id);
  }

  public function setDrSessionId(string $session_id)
  {
    return $this->setDrAttr(self::DR_SESSION_ID, $session_id);
  }

  public function setDrSourceId(string $source_id)
  {
    return $this->setDrAttr(self::DR_SOURCE_ID, $source_id);
  }

  public function setDrSubscriptionId(string $subscription_id)
  {
    $this->dr_subscription_id = $subscription_id;
    return $this->setDrAttr(self::DR_SUBSCRIPTION_ID, $subscription_id);
  }

  public function stop(string $status, string $stopReason = '', string $subStatus = Subscription::SUB_STATUS_NORMAL)
  {
    $this->setStatus($status);
    $this->stop_reason = $stopReason;
    $this->sub_status = $subStatus;

    $this->end_date = $this->start_date ? now() : null;
    $this->next_invoice_date = null;
    $this->active_invoice_id = null;
    $this->save();
  }

  public function getActiveInvoice(): Invoice|null
  {
    if (!$this->active_invoice_id) {
      return null;
    } else {
      return $this->invoices()->find($this->active_invoice_id);
    }
  }

  public function getInvoiceByOrderId(string $orderId): Invoice|null
  {
    return $this->invoices()->where('dr_order_id', $orderId)->first();
  }

  public function routeNotificationForMail($notification)
  {
    return [
      $this->billing_info['email'] => $this->billing_info['first_name'] . ' ' . $this->billing_info['last_name']
    ];
  }

  public function sendNotification(string $type, Invoice $invoice = null, array $context = [])
  {
    $context['subscription'] = $this;
    $context['invoice'] = $invoice;
    $this->notify(new SubscriptionNotification($type, $context));
  }
}
