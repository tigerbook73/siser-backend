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
        'old_plan_name'       => $helper->formatSubscriptionFullName($subscription),
        'new_plan_name'       => $helper->formatSubscriptionFullName($subscription, true),
        'next_invoice_date'   => $helper->formatDate($subscription->next_invoice_date),
      ]
    )
  !!}
  @else
  {!!
    $helper->trans(
      'subscription_reminder.notification',
      [
        'plan_name'           => $helper->formatSubscriptionFullName($subscription),
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
