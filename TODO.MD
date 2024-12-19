

step 2:

1. Send email to all stopped customers (paypal/googlepay)
2. prepare email
3. send email to select customers
4. cancell all subscriptions from digital-river
5. deatch webhook
6. stop payment failure
7.


step 3:

Backend:
1. check any dr active subscriptions, stop it if any
2. schedule: "subscription:to-paddle" command every 1 hour
3. command: "subscription:to-paddle"
   1. loop 50 customers (10 a group)
      1. send notification email
      2. update subscription->dr->transfer_started_at = now()
      3. delay 2 seconds for each group;
   2. if more customers, schedule next command in 2 minutes
4. SubscriptionService: stop active subscriptions when new subscription is created (better not create basic subscription)

Frontend:
1. Show special notification to customer
2. Add renew button to checkout
3. 

