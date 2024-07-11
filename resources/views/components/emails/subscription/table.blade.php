@props([ 'type', 'subscription', 'invoice' => null, 'refund' => null, 'fields', 'helper'])

<div>
  @if (in_array('order', $fields))
  <table>
    <tr>
      <td colspan="2" class="highlight">{{ $helper->trans('order.#', ['order_id' => $invoice->id]) }}</td>
    </tr>
    <tr>
      <td width="40%">DigitalRiver {{ $helper->trans('order.no') }}</td>
      <td>{{ $invoice->getDrOrderId() }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('order.date') }}</td>
      <td>{{ $helper->formatDate($invoice->invoice_date) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('order.type')}}</td>
      <td>{{ $helper->formatOrderType($invoice) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('order.status')}}</td>
      <td>{{ $helper->formatOrderStatus($invoice->status) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('order.total_amount')}}</td>
      <td>{{ $helper->formatPriceWithCurrency($invoice->total_amount) }}</td>
    </tr>
    @if ($invoice->total_refunded > 0)
    <tr>
      <td>{{ $helper->trans('order.total_refunded')}}</td>
      <td>{{ $helper->formatPriceWithCurrency($invoice->total_refunded) }}</td>
    </tr>
    @endif
  </table>
  @endif

  @if (in_array('customer', $fields))
  <table>
    <tr>
      <td width="40%" class="highlight">{{ $helper->trans('customer_info') }}</td>
      <td class="highlight">{{ $helper->trans('billing_address') }}</td>
    </tr>
    <tr>
      <td>
        <div>{{ $helper->formatName($subscription->billing_info) }}</div>
        <div>{{ $subscription->billing_info['email'] }}</div>
        <div>{{ $subscription->billing_info['phone'] }}</div>
      </td>
      <td>{!! $helper->formatAddress($subscription->billing_info['address']) !!}</td>
    </tr>
  </table>
  @endif

  @if (in_array('payment_method', $fields))
  <table>
    <tr>
      <td colspan="2" class="highlight">{{ $helper->trans('payment_method') }}</td>
    </tr>
    <tr>
      <td width="40%">{{ $helper->formatPaymentMethodType($subscription->user->payment_method->info()['type']) }}</td>
      <td>
        {!! $helper->formatPaymentMethod($subscription->user->payment_method->info()['type'], $subscription->user->payment_method->info()['display_data']) !!}
      </td>
    </tr>
  </table>
  @endif

  @if (in_array('items', $fields))
  <table>
    <tr>
      <td colspan="5" class="highlight">{{ $helper->trans('order_items', ['currency' => $invoice->currency]) }}</td>
    </tr>
    <tr>
      <th colspan="2" width="40%" class="text-left">{{ $helper->trans('order_item') }}</th>
      <th>{{ $helper->trans('order_price_excl') }}</th>
    </tr>
    @foreach ($invoice->items as $item)
    <tr>
      <td colspan="2">{{ $item['name'] }}</td>
      <td class="text-right">{{ $helper->formatPrice($item['price']) }}</td>
    </tr>
    @endforeach
    <tr>
      <td colspan="5" class="highlight"></td>
    </tr>
    <tr>
      <td colspan="5" class="highlight"></td>
    </tr>
    <tr>
      <td rowspan="5" width="40%"></td>
      <td>{{ $helper->trans('order_subtotal') }}</td>
      <td class="text-right">{{ $helper->formatPrice($invoice->subtotal) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->getTaxName() }}</td>
      <td class="text-right">{{ $helper->formatPrice($invoice->total_tax) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('order_total') }}</td>
      <td class="text-right">{{ $helper->formatPrice($invoice->total_amount) }}</td>
    </tr>
    @if ($type === 'subscription.order-refunded')
    <tr>
      <td>{{ $helper->trans('order_refunded') }}</td>
      <td class="text-right">{{ $helper->formatPrice($invoice->refunded_total ?? $invoice->total_amount) }}</td>
    </tr>
    @endif
  </table>
  @endif

  @if (in_array('subscription', $fields))
  <table>
    <tr>
      <td colspan="2" class="highlight">{{ $helper->trans('subscription.#', ['subscription_id' => $subscription->id]) }}</td>
    </tr>
    <tr>
      <td width="40%">{{ $helper->trans('subscription.plan_name') }}</td>
      <td>{{ $helper->formatSubscriptionPlanName($subscription) }}</td>
    </tr>
    @if ($subscription->license_package_info)
    <tr>
      <td >{{ $helper->trans('subscription.license_package') }}</td>
      <td>{{ $helper->formatSubscriptionLicenseName($subscription) }}</td>
    </tr>
    @endif
    <tr>
      <td>{{ $helper->trans('subscription.billing_period') }}</td>
      <td>{{ $helper->formatBillingPeriod($subscription) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('subscription.currency') }}</td>
      <td>{{ $subscription->currency }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('subscription.price') }}</td>
      <td>{{ $helper->formatPrice($subscription->price) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('subscription.total_amount') }}</td>
      <td>{{ $helper->formatPrice($subscription->total_amount) }}</td>
    </tr>

    @if ($helper->showStart($type))
    <tr>
      <td>{{ $helper->trans('subscription.start_date') }}</td>
      <td>
        {{ $helper->formatDate($subscription->start_date) }}
      </td>
    </tr>
    @endif

    @if ($helper->showEnd($type))
    <tr>
      <td>{{ $helper->trans('subscription.end_date') }}</td>
      <td>
        {{ $helper->formatDate($subscription->end_date) }}
      </td>
    </tr>
    @endif

    @if ($helper->showPeriod($type))
    <tr>
      <td>{{ $helper->trans('subscription.period_start_date') }}</td>
      <td>{{ $helper->formatDate($subscription->current_period_start_date) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('subscription.period_end_date') }}</td>
      <td>{{ $helper->formatDate($subscription->current_period_end_date) }}</td>
    </tr>
    @endif

    @if ($helper->showNextInvoice($type))
    <tr>
      <td colspan="5" class="highlight"></td>
    </tr>

    <tr>
      <td>{{ $helper->trans('subscription.next_invoice_plan') }}</td>
      <td>{{ $helper->formatSubscriptionPlanName($subscription, true) }}</td>
    </tr>
    @if ($subscription->license_package_info)
    <tr>
      <td >{{ $helper->trans('subscription.next_license_package') }}</td>
      <td>{{ $helper->formatSubscriptionLicenseName($subscription, true) }}</td>
    </tr>
    @endif
    <tr>
      <td>{{ $helper->trans('subscription.next_invoice_date') }}</td>
      <td>{{ $helper->formatDate($subscription->next_invoice_date) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('subscription.next_invoice_price') }}</td>
      <td>{{ $helper->formatPrice($subscription->next_invoice['price'] ) }}</td>
    </tr>
    <tr>
      <td>{{ $helper->trans('subscription.next_invoice_total_amount') }}</td>
      <td>{{ $helper->formatPrice($subscription->next_invoice['total_amount'] ) }}</td>
    </tr>
    @endif
  </table>
  @endif
</div>
