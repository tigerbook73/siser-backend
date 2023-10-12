migration:
1. add plan.next_plan_id
2. add plan.next_plan_info

migration data:
1. update annual plan.next_plan_id
2. update annual plan.next_plan_info
3. update annual subscription's plan_info (next_plan)

model:
1. plan.nextPlanInfo()

model:
1. create
   1. auto generate next_plan_id & info
   2. validate annual plan's prices
   3. validate monthly plan's prices
2. update
   1. validate annual plan's prices
   2. validate monthly plan's price
3. nextPlanInfo()
4. getNextPlan()
5. validatePlanPair()
6. update optionsAttributes




