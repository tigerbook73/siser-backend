## Step1: update user subscription level

```php

function migrate()
{
  User::chunkById(100, function ($users) {
    /**
     * @var \App\Models\User[] $users
     */
    foreach ($users as $user) {
      if ($user->seat_count != $user->license_count) {
        $user->updateSubscriptionLevel();
      }
    }
  });
}

```


## Step2: remove license count from user

+ remove from data base
+ remove all reference
