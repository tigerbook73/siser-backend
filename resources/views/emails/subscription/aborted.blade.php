<x-emails.subscription.layout :$subscription>
  Unfortunately, an attempted subscription to the <b>{{ $subscription->plan_info["name"] }}</b> failed. Please check
  your payment method and try again.<br />
  <br />
  Here is a summary of the attempted subscription purchase:<br />
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
      'total_amount'
    ]"
    :$timezone
  ></x-emails.subscription.table>
  <br />
</x-emails.subscription.layout>
