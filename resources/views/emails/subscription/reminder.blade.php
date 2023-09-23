<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper  
>
  @if ($subscription->isNextPlanDifferent())
  {!!
    $helper->trans(
      'subscription_reminder.notification_convert', 
      [
        'old_plan_name'       => $helper->formatSubscriptionPlanName($subscription),
        'new_plan_name'       => $helper->formatPlanName($subscription->next_invoice['plan_info'], $subscription->next_invoice['coupon_info']),
        'next_invoice_date'   => $helper->formatDate($subscription->next_invoice_date),
      ]
    ) 
  !!}
  @else 
  {!!
    $helper->trans(
      'subscription_reminder.notification', 
      [
        'plan_name'           => $helper->formatSubscriptionPlanName($subscription),
        'next_invoice_date'   => $helper->formatDate($subscription->next_invoice_date),
      ]
    ) 
  !!}
  @endif
  <br />
  <br />
  {{ $helper->trans('subscription_reminder.summary') }}
  <br />
  <br />
  <x-emails.subscription.table
    :$type
    :$subscription
    :fields="[
      'customer',
      'payment_method',
      'subscription',
    ]"
    :$helper
  />
</x-emails.subscription.layout>
