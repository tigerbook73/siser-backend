<x-emails.subscription.layout :$subscription>
  This is to inform you that the renewal of your subscription <b>{{ $subscription->plan_info['name'] }}</b> failed, and
  your subscription is terminated.<br />
  <br />
  Here is a brief summary of your subscription:<br />
  <br />
  <x-emails.subscription.table
    :$subscription
    :$invoice
    :fields="[
    'name',
    'end_date',
    'currency',
    'price',
    'total_discount',
    'subtotal',
    'total_tax', 
    'total_amount',
    ]"
    :$timezone
  >
  </x-emails.subscription.table>
  <br />
  You can check your subscription's details on our
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
