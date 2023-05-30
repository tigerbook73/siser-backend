<x-emails.subscription.layout :$subscription>
  We were unable to charge your
  {!!
    $subscription->user->payment_method->type == 'creditCard' ?
      '<b>' . strtoupper($subscription->user->payment_method->display_data['brand']) . '</b> card ending in <b>' . $subscription->user->payment_method->display_data['last_four_digits'] . '</b>' :
      '<b>' . ucfirst($subscription->user->payment_method->type) . '</b>'
  !!}
  for your subscription <b>{{$subscription->plan_info['name']}}</b
  >.<br />
  <br />
  To avoid disruptions to your subscription, please make sure
  <a href="https://software.siser.com/account/payment-method">your registered payment method</a> has enough funds. <br />
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
  >
  </x-emails.subscription.table>
  <br />
  You can check your subscription's details on our
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
