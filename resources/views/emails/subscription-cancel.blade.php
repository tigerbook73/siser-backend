<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>

<body>
  Hi {{ $subscription->user->full_name }},<br><br>

  Thank you for being a part of the subscription {{ $subscription->plan_info["name"] }}.<br>
  As you requested, we've canceled your membership effective {{ date("Y-m-d") }}.<br>
  We'd love to have you back, but we completely understand that this may not be the best option for you right now.<br><br>

  Thanks,<br>
  {{ config('app.name') }}<br>
  <img width="110px" src="https://www.siser.com/wp-content/themes/S22/images/layout/logo.png" />
</body>

</html>