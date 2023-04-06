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

  Thank you for making a decision to purchase a subscription from us!<br><br>

  We would like to let you know that your order has been accepted.<br><br>

  Here is your order number: {{ $subscription->id }}.<br><br>

  Below is a table details the subscription you have paid:<br>
  @include('emails.SubscriptionTable', array('subscription'=> $subscription))<br><br>

  If you have any questions or concerns about this order, feel free to reach out to our Customer Service anytime 9AM-5PM, Monday-Friday.<br>
  Be sure to have the order number handy so we can help you even faster!<br><br>

  Kind regards,<br>
  {{ config('app.name') }}<br>
  <img width="110px" src="https://www.siser.com/wp-content/themes/S22/images/layout/logo.png" />
</body>

</html>