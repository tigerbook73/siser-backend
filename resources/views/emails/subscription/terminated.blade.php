<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'subscription_terminated.notification',
      [
        'plan_name' => $helper->formatSubscriptionFullName($subscription),
        'end_date'  => $subscription->end_date
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_terminated.summary') }}
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
