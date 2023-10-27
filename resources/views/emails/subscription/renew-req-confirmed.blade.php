<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper  
>
  {!!
    $helper->trans(
      'subscription_renew_req_confirmed.notification', 
      [
        'plan_name'         => $helper->formatSubscriptionPlanName($subscription),
        'next_invoice_date' => $helper->formatDate($subscription->next_invoice_date),
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_renew_req_confirmed.summary') }}
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
