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

  This is a notification that your registered credit card with us for your monthly subscription remains decline for our multiple debit attempts.<br><br>

  Below is a table details the subscription you are currently subscribing:<br>
  @include('emails.SubscriptionTable', array('subscription'=> $subscription))<br><br>

  Please change or fill up your registered credit card with enough fund as soon as possible.<br><br>

  This will impact your current subscription to be canceled if our final notice being ignored.<br><br>

  Thanks,<br>
  {{ config('app.name') }}<br>
  <img width="110px" src="https://www.siser.com/wp-content/themes/S22/images/layout/logo.png" />
</body>

</html>