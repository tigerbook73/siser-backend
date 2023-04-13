<x-emails.subscription.layout :$subscription>
  This is a notification that your applied coupon for your monthly subscription has been expired.<br />
  <br />
  Below is a table that briefs the subscription you are currently subscribing:<br />

  <x-emails.subscription.table :$subscription></x-emails.subscription.table>
  <br />

  This will impact your next monthly subscription billing price back to the normal monthly subscription price of
  {{ $subscription->plan_info["price"]["currency"] . " " . number_format((float)$subscription->plan_info["price"]["price"], 2, '.', '') }}.
</x-emails.subscription.layout>
