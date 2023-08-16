# Step1: Update AWS env

aws ssm put-parameter --profile production --name "siser.DR_TOKEN" --value "xxxxxx" --type String --overwrite
aws ssm put-parameter --profile production --name "siser.DR_PUBLIC_KEY" --value "xxxxxx" --type String --overwrite
aws ssm put-parameter --profile production --name "siser.DR_DEFAULT_WEBHOOK" --value "xxxxxx" --type String --overwrite

# Step2: update data

1. general config: plan days

# Step3: update plan

art launch:step init
art launch:step update-countries
art launch:step update-plans

# step4: refresh user with level=2

in tinker:

foreach (User::where('subscription_level', '>', 1)->get() as $user) { $user->updateSubscriptionLevel(); }

# step5: test


