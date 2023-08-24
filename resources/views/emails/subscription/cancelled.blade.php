<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'messages.subscription_cancel.notification',
      [
        'plan_name' => $subscription->plan_info['name'],
        'date' => $helper->formatDate(now()),
        'end_date' => $helper->formatDate($subscription->current_period_end_date),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('messages.subscription_cancel.summary') }}
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
