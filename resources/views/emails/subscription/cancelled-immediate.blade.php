<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'subscription_cancel_immediate.notification',
      [
        'plan_name' => $helper->formatSubscriptionFullName($subscription),
        'end_date'  => $helper->formatDate(now()),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_cancel_immediate.summary') }}
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
