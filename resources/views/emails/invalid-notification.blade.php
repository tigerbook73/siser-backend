<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  <style>
    table,
    table tr td {
      padding: 2px 5px 2px 5px;
      border: 1px solid gray;
      border-collapse: collapse;
    }

    table {
      min-width: 600px;
      margin-top: 5px;
    }

    table tr td:first-child {
      width: 40%;
    }
  </style>
</head>
<body>
  <p>Hi,</p>
  <p>The notification "{{ $type }}" has an invalid subscription:</p>
  <table>
    <tr>
      <td>Subscription ID</td>
      <td>{{ $subscription->id }}</td>
    </tr>
    <tr>
      <td>Subscription Status</td>
      <td>{{ $subscription->status }}</td>
    </tr>
  </table>
</body>
</html>