<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'subscription_renew_expired.notification',
      [
        'plan_name' => $helper->formatSubscriptionFullName($subscription),
        'end_date'  => $helper->formatDate($subscription->end_date),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_renew_expired.summary') }}
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
