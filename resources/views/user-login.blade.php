<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Document</title>
  </head>

  <body>
    <script>
      const redirect = "{{ $redirect }}";
      const token = JSON.parse('{!! $token !!}');  // must be ' here 
      const account = JSON.parse('{!! $account !!}');  // must be ' here

      for (const prop in token) {
        window.sessionStorage.setItem("user_token." + prop, token[prop]);
      }
      for (const prop in account) {
        window.sessionStorage.setItem("user_account." + prop, account[prop]);
      }

      window.open(redirect || "/", "_self");
    </script>
  </body>
</html>
