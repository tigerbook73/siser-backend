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

  This is a notification that the automatic debit to your registered credit card for your monthly subscription did not go through successfully.<br><br>

  Below is a table details the subscription you are currently subscribing:<br>
  @include('emails.SubscriptionTable', array('subscription'=> $subscription))<br><br>

  We will make another attempt to debit your credit card again prior to that you have the time to change or fill up fund for your credit card.<br><br>

  If you have any questions or concerns about this order, feel free to reach out to our Customer Service anytime 9AM-5PM, Monday-Friday.<br>
  Be sure to have the order number handy so we can help you even faster!<br><br>

  Thanks,<br>
  {{ config('app.name') }}<br>
  <img width="110px" src="https://www.siser.com/wp-content/themes/S22/images/layout/logo.png" />
</body>

</html>