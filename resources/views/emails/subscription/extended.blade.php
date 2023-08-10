<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!! $helper->trans('messages.subscription_extended.notification', ['plan_name' => $subscription->plan_info['name']]) !!}
  <br />
  {{ $helper->trans('messages.subscription_extended.summary') }}
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
  <br />
  {{ $helper->trans('messages.order_confirm.agreement_claim') }}
</x-emails.subscription.layout>
