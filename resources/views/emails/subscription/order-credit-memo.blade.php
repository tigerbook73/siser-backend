<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'messages.subscription_order_credit.notification',
      [
        'plan_name' => $subscription->plan_info['name'],
        'invoice_pdf' => $invoice->pdf_file,
        'customer_portal_link' => $helper->getCustomerPortalLink(),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('messages.subscription_order_credit.summary') }}
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
