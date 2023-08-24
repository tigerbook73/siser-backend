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
      const token = JSON.parse(atob('{!! $token !!}'));  // must be ' here 
      const account = JSON.parse(atob('{!! $account !!}'));  // must be ' here
      const siserToken = JSON.parse(atob('{!! $siserToken !!}'));  // must be ' here

      for (const prop in token) {
        window.sessionStorage.setItem("user_token." + prop, token[prop]);
      }
      for (const prop in account) {
        window.sessionStorage.setItem("user_account." + prop, account[prop]);
      }
      for (const prop in siserToken) {
        window.sessionStorage.setItem("user_siserToken." + prop, siserToken[prop]);
      } 

      const redirect = window.sessionStorage.getItem('login_redirect') || "/";
      window.sessionStorage.removeItem('login_redirect');
      window.open(redirect || "/", "_self");
    </script>
  </body>
</html>
