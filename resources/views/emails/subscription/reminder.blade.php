<x-emails.subscription.layout :$subscription>
  We would like to remind you that your subscription to 
  <b>{{ $subscription->plan_info['name'] }}</b>
  is scheduled to renew on or after
  <b>{{ $subscription->next_invoice_date->setTimezone($timezone)->locale($subscription->billing_info['locale'])->isoFormat('lll z') }}</b>.<br />
  <br />
  To ensure uninterrupted access to all your subscription benefits, please ensure your
  {!!
    $subscription->user->payment_method->type == 'creditCard' ?
     '<b>' . strtoupper($subscription->user->payment_method->display_data['brand']) . '</b> card ending in <b>' . $subscription->user->payment_method->display_data['last_four_digits'] . '</b>' :
     '<b>' . ucfirst($subscription->user->payment_method->type) . '</b>'
  !!}
  has sufficient funds for the renewal amount of {{ $subscription->total_amount }} {{ $subscription->currency }}.<br />
  <br />
  Here is a summary of your subscription:<br />
  <br />
  <x-emails.subscription.table
    :$subscription
    :fields="[
      'name',
      'next_invoice_date',
      'currency',
      'price',
      'subtotal',
      'total_tax', 
      'total_amount'
    ]"
    :$timezone
  ></x-emails.subscription.table>
  <br />
  You can see your subscription details on our
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
