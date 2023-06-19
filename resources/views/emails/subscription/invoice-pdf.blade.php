<x-emails.subscription.layout :$subscription>
  We are pleased to provide the download link for your
  <a href="{{ $invoice->pdf_file }}" target="_blank">invoice pdf</a> for your subscription to the 
  <b>{{ $subscription->plan_info['name'] }}</b>.
  <br />
  <br />
  Click <a href="{{ $invoice->pdf_file }}" target="_blank">here</a> to see your invoice pdf.<br />
  <br />
  Here is a summary of your invoice:<br />
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
