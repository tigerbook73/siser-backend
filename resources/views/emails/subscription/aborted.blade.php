<x-emails.subscription.layout :$subscription>
  This is to inform you that your purchase of the subscription
  <b>{{ $subscription->plan_info["name"] }}</b> was cancelled. Please check your payment method and retry.<br />
  <br />
  Here is a brief summary of the subscription you are trying to purchase:<br />
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
      'total_amount'
    ]"
    :$timezone
  ></x-emails.subscription.table>
  <br />
</x-emails.subscription.layout>
