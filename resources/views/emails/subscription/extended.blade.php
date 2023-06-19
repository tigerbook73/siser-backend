<x-emails.subscription.layout :$subscription>
  We are pleased to confirm that your subscription to the 
  <b>{{ $subscription->plan_info['name'] }}</b>
  has been successfully renewed! You can continue enjoying all the exclusive benefits and features of your subscription.<br />
  <br />
  Here is a summary of your subscription:<br />
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
      'subtotal',
      'total_tax', 
      'total_amount',
    ]"
    :$timezone
  >
  </x-emails.subscription.table>
  <br />
  You can see your subscription details on our <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
