<x-emails.subscription.layout :$subscription>
  Thank you for being a part of the {{ $subscription->plan_info["name"] }}.<br>
  <br>
  We want to let you know that your current subscription has been successfully terminated.<br>
  <br>
  Below is a table that briefs the subscription:<br />
  <x-emails.subscription.table :$subscription></x-emails.subscription.table>
  <br />

  If this is caused by a fault please do not hesitate to contact us as soon as possible to have this rectifed.
</x-emails.subscription.layout>