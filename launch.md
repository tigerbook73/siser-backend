+ tinker: update all user's subscription_level to 0/1

  ```
  foreach (User::all() as $user) { $user->type = User::typeFromEmail($user->email); $user->updateSubscriptionLevel(); }
  ```

+ tinker: update all invoice's billing_info
+ ```
  foreach (Invoice::all() as $invoice) { $invoice->billing_info = $invoice->user->billing_info->info(); $invoice->save(); }
  ```

+ update countries
  ```
  art launch:step update-countries
  ```

+ update plan
  ```
  art launch:step update-plans
  ```

+ update dr plan
  ```
  art dr:cmd init
  ```

+ update webhooks
  ```
  art dr:cmd enable-hook
  ```

+ ip white list

