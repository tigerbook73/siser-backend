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
        padding: 2px 5px 2px 5px;
        border-top: 1px solid gray;
        border-bottom: 1px solid gray;
        border-collapse: collapse;
      }

      .subscription-table {
        min-width: 600px;
        border-top: 2px solid black;
        border-bottom: 2px solid black;
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
        <img width="110" height="46" src="{{ config('app.url') . '/imgs/siser-logo-trimmed.png'}}" />
      </div>
    </div>
  </body>
</html>
