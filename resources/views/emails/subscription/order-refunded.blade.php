<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'subscription_order_refunded.notification',
      [
        'order_id'        => $invoice->id,
        'plan_name'       => $helper->formatOrderPlanName($invoice),
        'amount'          => $helper->formatPriceWithCurrency($invoice->total_refunded),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_order_refunded.summary') }}
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
