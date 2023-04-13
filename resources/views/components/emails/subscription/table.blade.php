@props(['subscription'])

@php
$stopped
@endphp

<div>
  <table class="subscription-table">
    <tr>
      <th>Name</th>
      <th>Value</th>
    </tr>
    <tr>
      <td>Plan Name</td>
      <td>{{ $subscription->plan_info['name'] }}</td>
    </tr>
    <tr>
      <td>Currency</td>
      <td>
        {{ $subscription->plan_info['price']['currency'] }}
      </td>
    </tr>
    <tr>
      <td>Total Amount</td>
      <td>
        {{ number_format((float)$subscription->total_amount, 2) }}
      </td>
    </tr>
    <tr>
      <td>Billing Cycle</td>
      <td>Monthly</td>
    </tr>
    <tr>
      <td>Start Date</td>
      <td>{{ date("Y-m-d", strtotime($subscription->start_date)) }}</td>
    </tr>
    <tr>
      <td>End Date</td>
      <td>{{ $subscription->end_date ? date('Y-m-d', strtotime($subscription->end_date)) : '' }}</td>
    </tr>
    <tr>
      <td>Current Period No.</td>
      <td>{{ $subscription->current_period }}</td>
    </tr>
    <tr>
      <td>Current Period Start Date</td>
      <td>{{ $subscription->current_period_start_date }}</td>
    </tr>
    <tr>
      <td>Current Period End Date</td>
      <td>{{ $subscription->current_period_end_date }}</td>
    </tr>
    @if ($subscription->sub_status == 'cancelling')
    <tr>
      <td>To be terminated at</td>
      <td>{{ $subscription->current_period_end_date }}</td>
    </tr>
    @endif
    @if ($subscription->status == 'stopped' || $subscription->status == 'failed')
    <tr>
      <td>Terminated Reason</td>
      <td>{{ $subscription->stop_reason }}</td>
    </tr>
    @endif
    @if ($subscription->status == 'active')
    <tr>
      <td>Next invoice date</td>
      <td>{{ $subscription->next_invoice_date }}</td>
    </tr>
    @endif
  </table>
</div>
