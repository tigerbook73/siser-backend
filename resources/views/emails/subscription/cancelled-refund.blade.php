<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'messages.subscription_cancel_refund.notification',
      [
        'plan_name' => $subscription->plan_info['name'],
        'date' => $helper->formatDate(now()),
        'end_date' => $helper->formatDate(now()),
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('messages.subscription_cancel_refund.summary') }}
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
