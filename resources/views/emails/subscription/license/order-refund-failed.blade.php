<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'license_order_refund_failed.notification',
      [
        'order_id'  => $invoice->id,
        'type'      => $helper->trans('order.type.' . $invoice->type),
        'amount'    => $helper->formatPriceWithCurrency($refund->amount),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('license_order_refund_failed.summary') }}
  <br />
  <br />
  <x-emails.subscription.table
    :$type
    :$subscription
    :$invoice
    :$refund
    :fields="[
      'order',
      'customer',
      'items',
      'payment_method',
      'refund',
    ]"
    :$helper
  />
</x-emails.subscription.layout>
