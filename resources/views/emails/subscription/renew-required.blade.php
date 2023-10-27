<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper  
>
  {!!
    $helper->trans(
      'subscription_renew_required.notification', 
      [
        'plan_name'     => $helper->formatSubscriptionPlanName($subscription, true),
        'expire_date'   => $helper->formatDate($subscription->renewal_info['expire_at']),
        'renew_link'    => $helper->getRenewLink(),
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_renew_required.summary') }}
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
