<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {{-- TODO:  --}}
  {!!
    $helper->trans(
      'messages.subscription_order_refund_failed.notification',
      [
        'order_id' => $invoice->id,
        'plan_name' => $subscription->plan_info['name'],
        'amount' => $helper->formatPriceWithCurrency($refund->amount),
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('messages.subscription_order_refund_failed.summary') }}
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
