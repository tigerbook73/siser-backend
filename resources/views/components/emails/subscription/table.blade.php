@props([ 'type', 'subscription', 'invoice' => null, 'fields', 'helper'])

<div>
  @if (in_array('order', $fields))
  <table>
    <tr>
      <td colspan="2" class="highlight">{{ $helper->trans('messages.order.#', ['order_id' => $invoice->id]) }}</td>
    </tr>
    <tr>
      <td>DigitalRiver {{ $helper->trans('messages.order.no') }}</td>
      <td>{{ $invoice->getDrOrderId() }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('messages.order.date') }}</td>
      <td>{{ $helper->formatDate($invoice->invoice_date) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('messages.order.type')}}</td>
      <td>{{ $helper->formatOrderType($invoice->period) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('messages.order.status')}}</td>
      <td>{{ $helper->formatOrderStatus($invoice->status) }}</td>
    </tr>
  </table>
  @endif

  @if (in_array('customer', $fields))
  <table>
    <tr>
      <td class="highlight">{{ $helper->trans('messages.customer_info') }}</td>
      <td class="highlight">{{ $helper->trans('messages.billing_address') }}</td>
    </tr>
    <tr>
      <td>
        <div>{{ $helper->formatName($subscription->billing_info) }}</div>
        <div>{{ $subscription->billing_info['email'] }}</div>
        <div>{{ $subscription->billing_info['phone'] }}</div>
      </td>
      <td>
        {!! $helper->formatAddress($subscription->billing_info['address']) !!}
      </td>
    </tr>
  </table>
  @endif

  @if (in_array('items', $fields))
  <table>
    <tr>
      <td colspan="5" class="highlight">{{ $helper->trans('messages.order_items', ['currency' => $invoice->currency]) }}</td>
    </tr>
    <tr>
      <th class="text-left">{{ $helper->trans('messages.order_item') }}</th>
      <th>{{ $helper->trans('messages.order_quantity') }}</th>
      <th>{{ $helper->trans('messages.order_price_excl', ['tax' => $helper->getTaxName()]) }}</th>
    </tr>
    <tr>
      <td>{{ $invoice->plan_info['name'] }}</td>
      <td class="text-right">1</td>
      <td class="text-right">{{ $helper->formatPrice($invoice->plan_info['price']['price']) }}</td>
    </tr>
    <tr>
      <td colspan="5" class="highlight"></td>
    </tr>
    <tr>
      <td colspan="5" class="highlight"></td>
    </tr>
    <tr>
      <td rowspan="5" style="width: 40%"></td>
      <td class="text-right">{{ $helper->trans('messages.order_subtotal', ['tax' => $helper->getTaxName()]) }}</td>
      <td class="text-right">{{ $helper->formatPrice($invoice->subtotal) }}</td>
    </tr>
    <tr>
      <td class="text-right">{{ $helper->getTaxName() }}</td>
      <td class="text-right">{{ $helper->formatPrice($invoice->total_tax) }}</td>
    </tr>
    <tr>
      <td class="text-right">{{ $helper->trans('messages.order_total', ['tax' => $helper->getTaxName()]) }}</td>
      <td class="text-right">{{ $helper->formatPrice($invoice->total_amount) }}</td>
    </tr>
    {{-- TODO: adjust refund total --}}
    @if ($type === 'subscription.order-refunded')
    <tr>
      <td class="text-right">{{ $helper->trans('messages.order_refunded', ['tax' => $helper->getTaxName()]) }}</td>
      <td class="text-right">{{ $helper->formatPrice($invoice->refunded_total ?? $invoice->total_amount) }}</td>
    </tr>
    @endif
  </table>
  @endif

  @if (in_array('payment_method', $fields))
  <table>
    <tr>
      <td colspan="2" class="highlight">{{ $helper->trans('messages.payment_method') }}</td>
    </tr>
    <tr>
      <td>{{ $helper->formatPaymentMethodType($subscription->user->payment_method->info()['type']) }}</td>
      <td>
        {!! $helper->formatPaymentMethod($subscription->user->payment_method->info()['type'], $subscription->user->payment_method->info()['display_data']) !!}
      </td>
    </tr>
  </table>
  @endif
  
  @if (in_array('subscription', $fields))
  <table>
    <tr>
      <td colspan="2" class="highlight">{{ $helper->trans('messages.subscription_info') }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('messages.subscription_no') }}</td>
      <td>{{ $subscription->id }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('messages.subscription.plan_name') }}</td>
      <td>{{ $subscription->plan_info['name'] }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('messages.subscription.billing_period') }}</td>
      <td>{{ $helper->trans('messages.subscription.billing_period.monthly') }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('messages.subscription.currency') }}</td>
      <td>
        {{ $subscription->currency }}
      </td>
    </tr>
    <tr>
      <td>{{ $helper->trans('messages.subscription.price', ['tax' => $helper->getTaxName()]) }}</td>
      <td>
        {{ number_format($subscription->plan_info['price']['price'], 2) }}
      </td>
    </tr>
    <tr>
      <td>{{ $helper->getTaxName() }}</td>
      <td>
        {{ number_format($subscription->total_tax, 2) }}
      </td>
    </tr>
    <tr>
      <td>{{ $helper->trans('messages.subscription.total_amount', ['tax' => $helper->getTaxName()]) }}</td>
      <td>
        {{ number_format($subscription->total_amount, 2) }}
      </td>
    </tr>

    @if ($helper->showStart($type))
    <tr>
      <td>{{ $helper->trans('messages.subscription.start_date') }}</td>
      <td>
        {{ $helper->formatDate($subscription->start_date) }}
      </td>
    </tr>
    @endif

    @if ($helper->showEnd($type))
    <tr>
      <td>{{ $helper->trans('messages.subscription.end_date') }}</td>
      <td>
        {{ $helper->formatDate($subscription->end_date) }}
      </td>
    </tr>
    @endif
    
    @if ($helper->showPeriod($type))
    <tr>
      <td>{{ $helper->trans('messages.subscription.period_start_date') }}</td>
      <td>
        {{ $helper->formatDate($subscription->current_period_start_date) }}
      </td>
    </tr>
    @endif
    
    @if ($helper->showPeriod($type))
    <tr>
      <td>{{ $helper->trans('messages.subscription.period_end_date') }}</td>
      <td>
        {{ $helper->formatDate($subscription->current_period_end_date) }}
      </td>
    </tr>
    @endif
    
    @if ($helper->showNextInvoice($type))
    <tr>
      <td>{{ $helper->trans('messages.subscription.next_invoice_date') }}</td>
      <td>
        {{ $helper->formatDate($subscription->next_invoice_date) }}
      </td>
    </tr>
    @endif
  </table>
  @endif
</div>
