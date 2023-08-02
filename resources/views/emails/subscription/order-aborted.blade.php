<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!! $helper->trans('messages.subscription_order_abort.notification', ['plan_name' => $subscription->plan_info['name']]) !!}
  <br />
  <br />
  {{ $helper->trans('messages.subscription_order_abort.summary') }}
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
