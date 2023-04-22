<x-emails.subscription.layout :$subscription>
  We are sorry to inform you that your purchase of the subscription <strong>{{ $subscription->plan_info["name"] }}</strong> is failed.<br />
  <br />
  Please check your payment method and retry.<br />
  <br>
  Below is a table that briefs the subscription you tried to pay:<br />
  <x-emails.subscription.table :$subscription></x-emails.subscription.table>
  <br />

  If you have any questions or concerns about this order, feel free to reach out to our Customer Service anytime 9AM-5PM, Monday-Friday.<br />
</x-emails.subscription.layout>