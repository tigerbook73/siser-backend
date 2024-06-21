<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'subscription_failed.notification',
      [
        'plan_name' => $helper->formatSubscriptionFullName($subscription)
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_failed.summary') }}
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
</x-emails.subscription.layout>
