<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'license_cancel_refund.notification',
      [
        'plan_name' => $helper->formatSubscriptionFullName($subscription),
        'end_date'  => $helper->formatDate(now()),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('license_cancel_refund.summary') }}
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
