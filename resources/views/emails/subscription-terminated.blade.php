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

  Thank you for being a part of the {{ $subscription->plan_info["name"] }}.<br><br>

  We want to let you know that your current subscription has been successfully terminated.<br><br>

  If this is caused by a fault please do not hesitate to contact us as soon as possible to have this rectifed.<br><br>

  Thanks,<br>
  {{ config('app.name') }}<br>
  <img width="110px" src="https://www.siser.com/wp-content/themes/S22/images/layout/logo.png" />
</body>

</html>