<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!! $helper->trans('messages.order_confirm.notification', ['plan_name' => $subscription->plan_info['name']]) !!}
  <br />
  <br />
  {{ $helper->trans('messages.order_confirm.summary') }}
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
