<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'subscription_order_invoice.notification',
      [
        'order_id'              => $invoice->id,
        'plan_name'             => $helper->formatOrderPlanName($invoice),
        'invoice_pdf'           => $invoice->pdf_file,
        'customer_portal_link'  => $helper->getCustomerPortalLink(),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_order_invoice.summary') }}
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
