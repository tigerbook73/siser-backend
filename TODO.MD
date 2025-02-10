# License Package Feature

## Step1: refactor product/plan/coupon

### Refactor Product, Plan, and Coupon Models

   - one product -> multiple paddle product (interval-based).
   - coupon -> product restriction (interval-based).

### Migration

  - recreate paddle products
  - recreate paddle prices
  - refresh coupons

  - archive all paddle products
  - archive all paddle prices


## Step2: manage license packages

#### license package model

| license quantity / range | tiered discount |
| ------------------------ | --------------- |
| 1-10                     | 0%              |
| 11-20                    | 10%             |
| 21-25                    | 20%             |
| 30                       | 30%             |
| 50                       | 40%             |
| 100                      |                 |

Valid quantity: 1-25, 30, 50, 100

#### migration
  - remove previous license package
  - pre-create a new license package

## Step3: manage paddle prices

  - creating or updating paddle prices when plan / license-packages created and/or changes.
  - to make thing simple:
    - create new paddle prices
    - archive old paddle prices

### Step4: update subscription / invoice model

  - support new attributes
    - license number
    - license package id
  - update plan model?
    - query all prices for a plan?

### Step5: purchase license package - customer portal

  - add license number on list product page
  - add coupon on list product page

### update subscription / invoice model

  - support new attributes
    - credit -- customer level
  -

### update subscription

  - increase license number - charge immediately
  - decrease license number - credit
