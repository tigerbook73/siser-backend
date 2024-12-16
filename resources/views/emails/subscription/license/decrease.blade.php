<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      $subscription->license_package_info['quantity'] == ($subscription->next_invoice['license_package_info']['quantity'] ?? 0) ?
       'license_decrease.notification_immediate' :
       'license_decrease.notification',
      [
        'plan_name' => $helper->formatSubscriptionFullName($subscription),
        'end_date'  => $helper->formatDate($subscription->next_invoice_date),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('license_decrease.summary') }}
  <br />
  <br />
  <x-emails.subscription.table
    :$type
    :$subscription
    :fields="[
      'customer',
      'subscription',
    ]"
    :$helper
  />
</x-emails.subscription.layout>
