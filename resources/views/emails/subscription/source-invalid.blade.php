<x-emails.subscription.layout
  :$type
  :$subscription
  :$invoice
  :$helper
>
  {!!
    $helper->trans(
      'subscription_source_invalid.notification',
      [
        'plan_name' => $helper->formatSubscriptionPlanName($subscription),
        'collection_end_date' => $helper->formatDate($subscription->getNextInvoiceCollectionEndDate())
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_source_invalid.summary') }}
  <br />
  <br />
  <x-emails.subscription.table
    :$type
    :$subscription
    :fields="[
      'customer',
      'payment_method',
      'subscription',
    ]"
    :$helper
  />
</x-emails.subscription.layout>
