<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>

<body>
  Dear {{ $subscription->user->full_name }},<br><br>

  This is a notification that your applied coupon for your monthly subscription has been expired.<br><br>

  Below is a table details the subscription you are currently subscribing:<br>
  @include('emails.SubscriptionTable', array('subscription'=> $subscription))<br><br>

  This will impact your next monthly subscription billing price back to the normal monthly subscription price of {{ $subscription->plan_info["price"]["currency"] . " " . number_format((float)$subscription->plan_info["price"]["price"], 2, '.', '') }}.<br><br>

  Thanks,<br>
  {{ config('app.name') }}<br>
  <img width="110px" src="https://www.siser.com/wp-content/themes/S22/images/layout/logo.png" />
</body>

</html>