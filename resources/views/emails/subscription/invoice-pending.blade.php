<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'subscription_invoice_pending.notification',
      [
        'plan_name' => $helper->formatSubscriptionPlanName($subscription)
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_invoice_pending.summary') }}
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
