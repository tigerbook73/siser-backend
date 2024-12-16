<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      $invoice->isNewLicensePackageOrder() ? 'license_order_confirm.notification_new': 'license_order_confirm.notification_increase',
      [
        'plan_name' => $invoice->plan_info['name'],
        'license_package_name' => $invoice->license_package_info['name'],
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('license_order_confirm.summary') }}
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
  <br />
  {{ $helper->trans('license_order_confirm.agreement_claim') }}
</x-emails.subscription.layout>
