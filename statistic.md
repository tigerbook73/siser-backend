

## customer statistic


### every day statics

#### Customer Data

+ total_users
+ total_machines
+ total_machine_owners

+ total_level_#_users
+ total_level_#_machine_owners
+ total_level_#_non_machine_owners

#### Subscription data

+ subscription_level_#_count
+ subscription_level_#_inc
+ subscription_level_#_dec

+ subscription_level_#_month_plan_count
+ subscription_level_#_month_plan_inc
+ subscription_level_#_month_plan_dec

+ subscription_level_#_year_plan_count
+ subscription_level_#_year_plan_inc
+ subscription_level_#_year_plan_dec


SubscriptionLog:

event:
1. subscription.activated
2. subscription.cancelled
3. subscription.terminated
4. subscription.extended
5. subscription.failed

data:
1. user_id
2. subscription_id
3. event
4. event_data
   1. subscription_info
5. timestamp


1. user_id
2. subscription_id
3. invoice_id
4. subscription_level
5. timestamp
6. event
7. event_data
   1. 