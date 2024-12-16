<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'license_order_refunded.notification',
      [
        'order_id'        => $invoice->id,
        'type'            => $helper->trans('order.type.' . $invoice->type),
        'currency'        => $invoice->currency,
        'refund_total'    => $helper->formatPrice($invoice->total_refunded),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('license_order_refunded.summary') }}
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
