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

  This is a curtesy reminder that your monthly subscription renewal is soon to be due on {{ date("Y-m-d", strtotime($subscription->next_invoice_date)) }}.<br><br>

  Below is a table details the subscription you are currently subscribing:<br>
  @include('emails.SubscriptionTable', array('subscription'=> $subscription))<br><br>

  Please make sure that your credit card registered with us has enough fund for this coming debit charge.<br><br>

  Thanks,<br>
  {{ config('app.name') }}<br>
  <img width="110px" src="https://www.siser.com/wp-content/themes/S22/images/layout/logo.png" />
</body>

</html>