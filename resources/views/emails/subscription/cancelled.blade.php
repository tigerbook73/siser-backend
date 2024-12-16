<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'subscription_cancel.notification',
      [
        'plan_name' => $helper->formatSubscriptionFullName($subscription),
        'end_date'  => $helper->formatDate($subscription->end_date),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_cancel.summary') }}
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
