@props(['subscription'])

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Document</title>

    <style>
      .mail-content {
        line-height: 1.3em;
        font-size: 1.1em;
        margin: auto;
        max-width: 1400px;
      }

      .subscription-table,
      .subscription-table th,
      .subscription-table td {
        padding: 0px 5px 0px 5px;
        border: 1px solid black;
        border-collapse: collapse;
      }

      .subscription-table th {
        background-color: #1976d2;
        color: white;
        font-weight: bold;
        text-align: left;
        min-width: 200px;
      }
    </style>
  </head>

  <body>
    <div class="mail-content">
      <!-- greeting -->
      <div>
        Dear {{ $subscription->billing_info['first_name'] . ' ' . $subscription->billing_info['last_name'] }},<br />
        <br />
      </div>

      <!-- main content -->
      <div>
        {{ $slot }}
      </div>

      <!-- thank you -->
      <div>
        <br />
        Kind Regards,<br />
        {{ config("app.name") }}<br />
      </div>

      <!-- logo -->
      <div>
        <img width="110px" src="https://www.siser.com/wp-content/themes/S22/images/layout/logo.png" />
      </div>
    </div>
  </body>
</html>
