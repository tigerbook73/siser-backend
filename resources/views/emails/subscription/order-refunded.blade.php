<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'messages.subscription_order_refunded.notification',
      [
        'plan_name' => $subscription->plan_info['name'],
        'currency' => $invoice->currency,
        'refund_total' => $helper->formatPrice($invoice->total_amount),
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('messages.subscription_order_refunded.summary') }}
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
