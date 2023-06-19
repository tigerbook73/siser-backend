<x-emails.subscription.layout :$subscription>
  We regret to inform you that we could not process the payment for your
  <b>{{ $subscription->plan_info['name'] }}</b>
  subscription using the 
  {!!
    $subscription->user->payment_method->type == 'creditCard' ?
     '<b>' . strtoupper($subscription->user->payment_method->display_data['brand']) . '</b> card ending in <b>' . $subscription->user->payment_method->display_data['last_four_digits'] . '</b>' :
     '<b>' . ucfirst($subscription->user->payment_method->type) . '</b>' 
  !!}
  .<br />
  <br />
  To prevent any disruption to your subscription access, we kindly request that you verify
  <a href="https://software.siser.com/account/payment-method">your registered payment method</a>
  and ensure sufficient funds are available.<br />
  <br />
  If you require any assistance or have any questions regarding your payment, please contact our support team.<br />
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
  You can see your subscription details on our
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
