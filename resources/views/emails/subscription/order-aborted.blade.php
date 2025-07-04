<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'subscription_order_abort.notification',
      [
        'plan_name' => $helper->formatOrderPlanName($invoice)
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_order_abort.summary') }}
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
