<x-emails.subscription.layout :$subscription>
  This is a curtesy reminder that your monthly subscription renewal is soon to be due on
  {{ $subscription->next_invoice_date->toDateTimeString() }}.<br />
  <br />
  Below is a table that briefs the subscription you are currently subscribing:<br />
  
  <x-emails.subscription.table :$subscription></x-emails.subscription.table>
  <br />  

  Please make sure that your credit card registered with us has enough fund for this coming debit charge.
</x-emails.subscription.layout>
