<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper  
>
  {!!
    $helper->trans(
      'messages.subscription_reminder.notification', 
      [
        'plan_name'           => $subscription->plan_info['name'],
        'next_invoice_date'   => $helper->formatDate($subscription->next_invoice_date),
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('messages.subscription_reminder.summary') }}
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
