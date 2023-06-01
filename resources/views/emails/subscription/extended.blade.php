<x-emails.subscription.layout :$subscription>
  This is to confirm that your subscription <b>{{ $subscription->plan_info['name'] }}</b> has been successfully
  renewed!<br />
  <br />
  Here is a brief summary of your subscription:<br />
  <br />
  <x-emails.subscription.table
    :$subscription
    :$invoice
    :fields="[
      'name',
      'period_start_date',
      'period_end_date',
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
