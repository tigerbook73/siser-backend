<x-emails.subscription.layout :$subscription>
  We are pleased to inform you that your subscription to the <b>{{ $subscription->plan_info['name'] }}</b> has been
  confirmed!<br />
  <br />
  Here is a summary of your subscription:<br />
  <br />
  <x-emails.subscription.table
    :$subscription
    :$invoice
    :fields="[
      'order_id',
      'name',
      'start_date',
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
  You can see your subscription details on our
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
