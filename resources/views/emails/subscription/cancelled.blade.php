<x-emails.subscription.layout :$subscription>
  This is to inform you that we've received your cancelling request for the subscription
  <b>{{ $subscription->plan_info["name"] }}</b> on
  <b>{{ now()->setTimezone('Australia/Melbourne')->toRfc850String() }}</b
  >. And your subscription will be terminated on
  <b>{{$subscription->current_period_end_date->setTimezone('Australia/Melbourne')->toRfc850String()}}</b
  >.<br />
  <br />
  Here is a brief summary of your subscription:<br />
  <br />
  <x-emails.subscription.table
    :$subscription
    :$invoice
    :fields="[
      'name',
      'currency',
      'price',
      'total_discount',
      'subtotal',
      'total_tax', 
      'total_amount',
      'end_date',
    ]"
  ></x-emails.subscription.table>
  <br />
  You can check your subscription's details on our
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
