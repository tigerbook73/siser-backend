<x-emails.subscription.layout
  :$type
  :$subscription
  :$helper
>
  {!! 
    $helper->trans(
      'subscription_plan_updated_other.notification',
      [
        'plan_name' => $helper->formatSubscriptionPlanName($subscription),
      ]
    ) 
  !!}
  <br />
  <br />
  {{ $helper->trans('subscription_plan_updated_other.summary') }}
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
