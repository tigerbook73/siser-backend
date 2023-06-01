@props(['subscription', 'invoice' => null, 'fields', 'timezone'])

<div>
  <table class="subscription-table">
    @if (in_array('order_id', $fields))
    <tr>
      <td>Order Id</td>
      <td>{{ $subscription->getDrOrderId() }}</td>
    </tr>
    @endif 

    @if (in_array('id', $fields))
    <tr>
      <td>Subscription Id</td>
      <td>{{ $subscription->id }}</td>
    </tr>
    @endif 

    @if (in_array('name', $fields))
    <tr>
      <td>Plan Name</td>
      <td>{{ ($invoice ?? $subscription)->plan_info['name'] }}</td>
    </tr>
    @endif

    @if (in_array('description', $fields))
    <tr>
      <td>Description</td>
      <td>{{ ($invoice ?? $subscription)->plan_info['description'] }}</td>
    </tr>
    @endif

    @if (in_array('period', $fields))
    <tr>
      <td>Period No.</td>
      <td>{{ $invoice?->period ?? $subscription->current_period }}</td>
    </tr>
    @endif

    @if (in_array('currency', $fields))
    <tr>
      <td>Currency</td>
      <td>
        {{ $subscription->currency }}
      </td>
    </tr>
    @endif 

    @if (in_array('price', $fields))
    <tr>
      <td>Price</td>
      <td>
        {{ number_format(($invoice ?? $subscription)->plan_info['price']['price'], 2) }}
      </td>
    </tr>
    @endif 

    @if (in_array('total_discount', $fields))
    <tr>
      <td>Total Discount</td>
      <td>
        {{ number_format((float)($invoice ?? $subscription)->total_discount, 2) }}
      </td>
    </tr>
    @endif

    @if (in_array('subtotal', $fields))
    <tr>
      <td>Subtotal</td>
      <td>
        {{ number_format((float)($invoice ?? $subscription)->subtotal, 2) }}
      </td>
    </tr>
    @endif

    @if (in_array('total_tax', $fields))
    <tr>
      <td>Total Tax</td>
      <td>
        {{ number_format((float)($invoice ?? $subscription)->total_tax, 2) }}
      </td>
    </tr>
    @endif

    @if (in_array('total_amount', $fields))
    <tr>
      <td>Total Amount</td>
      <td>
        {{ number_format((float)($invoice ?? $subscription)->total_amount, 2) }}
      </td>
    </tr>
    @endif

    @if (in_array('start_date', $fields))
    <tr>
      <td>Start Date</td>
      <td>{{ $subscription->start_date ? $subscription->start_date->setTimezone($timezone)->isoFormat('lll z') : '' }}</td>
    </tr>
    @endif

    @if (in_array('end_date', $fields))
    <tr>
      <td>End Date</td>
      <td>{{ $subscription->end_date ? $subscription->end_date->setTimezone($timezone)->isoFormat('lll z') : '' }}</td>
    </tr>
    @endif

    @if (in_array('period_start_date', $fields))
    <tr>
      <td>Period Start Date</td>
      <td>{{ ($invoice?->period_start_date ?? $subscription->current_period_start_date)->setTimezone($timezone)->isoFormat('lll z') }}</td>
    </tr>
    @endif

    @if (in_array('period_end_date', $fields))
    <tr>
      <td>Period End Date</td>
      <td>{{ ($invoice?->period_end_date ?? $subscription->current_period_end_date)->setTimezone($timezone)->isoFormat('lll z') }}</td>
    </tr>
    @endif

    @if (in_array('terminate_date', $fields))
    <tr>
      <td>To be terminated at</td>
      <td>{{ $subscription->current_period_end_date->setTimezone($timezone)->isoFormat('lll z') }}</td>
    </tr>
    @endif

    @if (in_array('terminate_reason', $fields))
    <tr>
      <td>Terminated Reason</td>
      <td>{{ $subscription->stop_reason }}</td>
    </tr>
    @endif

    @if (in_array('next_invoice_date', $fields))
    <tr>
      <td>Next invoice date</td>
      <td>{{ $subscription->next_invoice_date->setTimezone($timezone)->isoFormat('lll z') }}</td>
    </tr>
    @endif
  </table>
</div>
