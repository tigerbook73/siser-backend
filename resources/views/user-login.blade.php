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
      const token = JSON.parse('{!! $token !!}');
      const account = JSON.parse('{!! $account !!}');

      window.sessionStorage.setItem("user_token.access_token", token.access_token);
      window.sessionStorage.setItem("user_token.expires_in", token.expires_in);
      window.sessionStorage.setItem("user_token.token_type", token.token_type);

      window.sessionStorage.setItem("user_account.name", account.name);
      window.sessionStorage.setItem("user_account.full_name", account.full_name);
      window.sessionStorage.setItem("user_account.email", account.email);
      window.open(redirect);
    </script>
  </body>
</html>
