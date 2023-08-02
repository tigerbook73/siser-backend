<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'messages.subscription_order_cancel.notification',
      [
        'plan_name' => $subscription->plan_info['name'],
        'date' => $helper->formatDate(now()),
        'end_date' => $helper->formatDate(now()),
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('messages.subscription_order_cancel.summary') }}
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
    ]"
    :$helper
  />
</x-emails.subscription.layout>
