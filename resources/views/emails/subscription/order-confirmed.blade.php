<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'subscription_order_confirm.notification',
      [
        'plan_name' => $helper->formatOrderPlanName($invoice)
      ]
    )
  !!}
  <br />
  <br />
  @if ($subscription->isFreeTrial())
  {!!
    $helper->trans(
      'subscription_order_confirm.free_claim',
      [
        'free_trial_end_date' => $helper->formatDate($subscription->current_period_end_date),
        'standard_plan' => $helper->formatPlanName($subscription->next_invoice['plan_info'], $subscription->next_invoice['coupon_info']),
      ]
    )
  !!}
  <br />
  <br />
  @elseif ($subscription->renewal_info)
  {!!
    $helper->trans(
      'subscription_order_confirm.manual_renewal',
      [
        'plan_end_date' => $helper->formatDate($subscription->current_period_end_date),
      ]
    )
  !!}
  <br />
  <br />
  @elseif ($subscription->isFixedTermPercentage())
  {!!
    $helper->trans(
      'subscription_order_confirm.percentage_claim',
      [
        'coupon_end_date' => $helper->formatDate($subscription->start_date->addUnit($subscription->coupon_info['interval'], $subscription->coupon_info['interval_count'])),
        'standard_plan' => $subscription->plan_info['name']
      ]
    )
  !!}
  <br />
  <br />
  @endif
  {{ $helper->trans('subscription_order_confirm.summary') }}
  <br />
  <br />
  <x-emails.subscription.table
    :$type
    :$subscription
    :$invoice
    :fields="[
      'order',
      'customer',
      'items',
      'payment_method',
      'subscription',
    ]"
    :$helper
  />
  <br />
  {{ $helper->trans('subscription_order_confirm.agreement_claim') }}
</x-emails.subscription.layout>
