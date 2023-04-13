<x-emails.subscription.layout :$subscription>
  Please find here is the download link for your <a href="{{ $invoice->pdf_file }}" target="_blank">invoice pdf</a>.<br />
  <br />
  Below is a table that briefs the subscription you are currently subscribing:

  <x-emails.subscription.table :$subscription></x-emails.subscription.table>
  <br />

  If you have any questions or concerns about this order, feel free to reach out to our Customer Service anytime 9AM-5PM, Monday-Friday.
</x-emails.subscription.layout>
