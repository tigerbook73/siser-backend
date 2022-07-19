<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

  <script>
    const redirect = {{$success}} && {{$online}} && {{$op == 'reg'}};
    if (redirect){
      const redirect_url = "{!! $display_data['redirect_url'] ?? "" !!}";
      alert("REDIRECT TO: " + redirect_url);
      window.open(redirect_url, "_self");
    }
  </script>

  <style>
    table, th, td {
      border: 1px solid black;
    }    
  </style>
</head>

<body>
  <h3>{{$title}}</h3>
  <h4>{{$error_message}}</h4>
  @if ($success)
  <hr/>
  <h4>reg record</h4>
  <table>
    <tr>
      <th>Property</th>
      <th>Value</th>
    </tr>
    @foreach ($reg_data as $field => $value)
    <tr>
      <td>{{$field}}</td>
      <td>{{$value}}</td>
    </tr>
    @endforeach
  </table>

  <h4>display data</h4>
  <table>
    <tr>
      <th>Property</th>
      <th>Value</th>
    </tr>
    @foreach ($display_data as $field => $value)
    <tr>
      <td>{{$field}}</td>
      <td>{{$value}}</td>
    </tr>
    @endforeach
  </table>

  @endif
</body>

</html>