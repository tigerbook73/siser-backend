<style>
  .outlineTable,
  .outlineTable th,
  .outlineTable td {
    padding: 5px;
    border: 1px solid black;
    border-collapse: collapse;
  }

  .outlineTable th {
    background-color: #1976d2;
    color: white;
    font-weight: bold;
  }
</style>
<table class="outlineTable">
  <tr>
    <th>Item Name</th>
    <th></th>
  </tr>
  <tr>
    <td>Plan Name</td>
    <td> {{ $subscription->plan_info["name"] }}</td>
  </tr>
  <tr>
    <td>Price</td>
    <td> {{ $subscription->plan_info["price"]["currency"] . ' ' . number_format((float)$subscription->plan_info["price"]["price"], 2, '.', '') }}</td>
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
    <td>{{ date("Y-m-d", strtotime($subscription->end_date)) }}</td>
  </tr>
  <tr>
    <td>Status</td>
    <td>{{ $subscription->status }}</td>
  </tr>
  <tr>
    <td>Subscription Level</td>
    <td>{{ $subscription->subscription_level }}</td>
  </tr>
</table>