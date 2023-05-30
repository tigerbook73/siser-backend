<x-emails.subscription.layout :$subscription>
  This is to inform you that your subscription <b>{{ $subscription->plan_info["name"] }}</b> was terminated on
  <b>{{$subscription->current_period_end_date->setTimezone('Australia/Melbourne')->toRfc850String()}}</b
  >.<br />
  <br />
  You can check your subscription's details on our
  <a href="https://software.siser.com/account/subscription">Customer Portal</a>.<br />
  <br />
</x-emails.subscription.layout>
