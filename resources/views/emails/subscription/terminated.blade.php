<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!! 
    $helper->trans(
      'messages.subscription_terminated.notification',
      [
        'plan_name' => $subscription->plan_info['name'],
        'end_date' => $subscription->end_date
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('messages.subscription_terminated.summary') }}
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
