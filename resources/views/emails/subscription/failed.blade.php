<x-emails.subscription.layout :$subscription>
  We are writing to inform you that the renewal charge for your 
  <b>{{ $subscription->plan_info['name'] }}</b>
  subscription has failed and your subscription has been terminated.
  <br />
  <br />
  We apologise for the inconvenience caused and request that you kindly repurchase the software if you wish
  to continue using the product, or contact our support team for assistance.<br />
  <br />
  Here is a summary of your subscription:<br />
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
  You can see your subscription details on our 
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
