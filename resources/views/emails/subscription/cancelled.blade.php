<x-emails.subscription.layout :$subscription>
  Thank you for being a part of the subscription <strong>{{ $subscription->plan_info["name"] }}</strong>.<br />
  <br />
  As you requested, we've canceled your membership effective on {{ date("Y-m-d") }}.<br />
  <br>
  Below is a table that briefs the subscription you have paid:<br />
  <x-emails.subscription.table :$subscription></x-emails.subscription.table>
  <br />

  We'd love to have you back, but we completely understand that this may not be the best option for you right now.<br />
</x-emails.subscription.layout>
