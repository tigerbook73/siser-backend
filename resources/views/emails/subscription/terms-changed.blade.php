<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!!
    $helper->trans(
      'subscription_terms_changed.notification',
      [
        'plan_name'     => $subscription->plan_info['name'],
        'terms'         => $helper->getTermsLink(),
        'terms_items'   => '<ul><li>Terms item 1</li><li>Terms item 2</li></ul>',
      ]
    )
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_terms_changed.summary') }}
  <br />
  <br />
  <x-emails.subscription.table
    :$type
    :$subscription
    :$invoice
    :fields="[
      'order',
      'customer',
      'subscription',
    ]"
    :$helper
  />
</x-emails.subscription.layout>
