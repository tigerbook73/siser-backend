<x-emails.subscription.layout :$subscription>
  This is a reminder that your
  <b>{{$subscription->plan_info['name']}}</b>
  subscription is set to renew on or after
  <b>{{ $subscription->next_invoice_date->setTimezone('Australia/Melbourne')->toRfc850String() }}</b
  >.<br />
  <br />
  To ensure that your subscription continues without interruption, your
  {!!
    $subscription->user->payment_method->type == 'creditCard' ?
      '<b>' . strtoupper($subscription->user->payment_method->display_data['brand']) . '</b> card ending in <b>' . $subscription->user->payment_method->display_data['last_four_digits'] . '</b>' :
      '<b>' . ucfirst($subscription->user->payment_method->type) . '</b>'
  !!}
  will be charged {{$subscription->total_amount}} {{$subscription->currency}}.<br />
  <br />
  Here is a brief summary of your subscription:<br />
  <br />
  <x-emails.subscription.table
    :$subscription
    :fields="[
      'name',
      'next_invoice_date',
      'currency',
      'price',
      'total_discount',
      'subtotal',
      'total_tax', 
      'total_amount'
    ]"
  ></x-emails.subscription.table>
  <br />
  You can check your subscription's details on our
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
