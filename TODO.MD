
- [License Package Feature](#markdown-header-license-package-feature)
  - [Step1: refactor product/plan/coupon](#markdown-header-step1-refactor-productplancoupon)
    - [Refactor Product, Plan, and Coupon Models](#markdown-header-refactor-product-plan-and-coupon-models)
    - [Migration](#markdown-header-migration)
  - [Step2: manage license packages](#markdown-header-step2-manage-license-packages)
  - [Step3: manage paddle prices](#markdown-header-step3-manage-paddle-prices)
    - [update plan model](#markdown-header-update-plan-model)
    - [manage plan model](#markdown-header-manage-plan-model)
  - [Step4: update subscription / invoice model to support license package](#markdown-header-step4-update-subscription-invoice-model-to-support-license-package)
  - [Step5: purchase license package - customer portal](#markdown-header-step5-purchase-license-package-customer-portal)
  - [Step6: update subscription / invoice model to support credit/items/prorate](#markdown-header-step6-update-subscription-invoice-model-to-support-credititemsprorate)
  - [Step7: manage subscription increate/decrease license number](#markdown-header-step7-manage-subscription-increatedecrease-license-number)
  - [Step8: finalize admin portal to support license package](#markdown-header-step8-finalize-admin-portal-to-support-license-package)

# License Package Feature

## Step1: refactor product/plan/coupon

refactor product/plan/coupon to optimize product-price-discount relationship.

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

refactor license package model to support tiered discount.

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

creating or updating paddle prices when plan / license-packages created and/or changed.

### update plan model

update plan model to support list of paddle prices.

### manage plan model

- to make thing simple:
  - create new paddle prices
  - archive old paddle prices

## Step4: update subscription / invoice model to support license package

update subscription and invoice model to support new attributes (license_number, license_package_id).

  - support new attributes
    - license number
    - license package id
  - update plan model?
    - query all prices for a plan?

## Step5: purchase license package - customer portal

  - add license number on list product page
  - add coupon on list product page

## Step6: update subscription / invoice model to support credit/items/prorate

  - subscription:
    - new/update attributes
      - credit -- customer level
      - items -- new items
      - prorated -- prorated amount ?
  - invoice:
    - new/update attributes
      - credit -- customer level
      - items -- new items
      - prorated -- prorated amount ?
    -
  -

## Step7: manage subscription increate/decrease license number

  - increase license number - charge immediately
  - decrease license number - credit

## Step8: finalize admin portal to support license package


