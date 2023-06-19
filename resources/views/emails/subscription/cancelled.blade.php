<x-emails.subscription.layout :$subscription>
  We’re sorry to see you go! Your <b>{{ $subscription->plan_info["name"] }}</b> subscription was cancelled as per your
  request on
  <b>{{ now()->setTimezone($timezone)->locale($subscription->billing_info['locale'])->isoFormat('lll z') }}</b>. You
  can still access your benefits until your subscription is terminated on
  <b>{{ $subscription->current_period_end_date->setTimezone($timezone)->locale($subscription->billing_info['locale'])->isoFormat('lll z') }} </b>.<br />
  <br />
  Thank you for your past support, and please feel free to contact us if you have any questions or require further assistance.<br />
  <br />
  Here is a summary of your cancelled subscription:<br />
  <br />
  <x-emails.subscription.table
    :$subscription
    :$invoice
    :fields="[
      'name',
      'currency',
      'price',
      'subtotal',
      'total_tax', 
      'total_amount',
      'end_date',
    ]"
    :$timezone
  ></x-emails.subscription.table>
  <br />
  You can see your subscription details on our 
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
