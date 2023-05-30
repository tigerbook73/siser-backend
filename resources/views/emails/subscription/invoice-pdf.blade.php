<x-emails.subscription.layout :$subscription>
  Please find here the download link for your
  <a href="{{ $invoice->pdf_file }}" target="_blank">invoice pdf</a> for your subscription
  <b>{{$subscription->plan_info['name']}}</b
  >.<br />
  <br />
  Here is a brief summary of your invoice:<br />
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
