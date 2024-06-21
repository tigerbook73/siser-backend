<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'subscription_plan_updated_german.notification',
      [
        'plan_name' => $helper->formatSubscriptionFullName($subscription),
        'end_date' => $helper->formatDate($subscription->current_period_end_date),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_plan_updated_german.summary') }}
  <br />
  <br />
  <x-emails.subscription.table
    :$type
    :$subscription
    :fields="[
      'customer',
      'subscription',
    ]"
    :$helper
  />
</x-emails.subscription.layout>
