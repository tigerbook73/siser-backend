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

  Thank you for your recent monthly payment for continuing a subscription.<br><br>

  Below is a table details the subscription you have paid:<br>
  @include('emails.SubscriptionTable', array('subscription'=> $subscription))<br><br>

  As the payment was successful your current subscription has been extended for another month.<br><br>

  Thanks,<br>
  {{ config('app.name') }}<br>
  <img width="110px" src="https://www.siser.com/wp-content/themes/S22/images/layout/logo.png" />
</body>

</html>