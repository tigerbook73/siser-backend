<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'license_order_credit.notification',
      [
        'order_id'              => $invoice->id,
        'license_package_name'  => $invoice->findLicenseItem()['name'] ?? "",
        'credit_memo'           => $credit_memo,
        'customer_portal_link'  => $helper->getCustomerPortalLink(),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('license_order_credit.summary') }}
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
