<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'license_cancel.notification',
      [
        'plan_name' => $subscription->plan_info['name'],
        'end_date'  => $helper->formatDate($subscription->next_invoice_date),
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('license_cancel.summary') }}
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
