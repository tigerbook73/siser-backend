
- [License Package Feature](#markdown-header-license-package-feature)
  - [Step1: refactor product/plan/coupon (DONE)](#markdown-header-step1-refactor-productplancoupon-done)
    - [Refactor Product, Plan, and Coupon Models (DONE)](#markdown-header-refactor-product-plan-and-coupon-models-done)
    - [Migration (DONE)](#markdown-header-migration-done)
  - [Step2: manage license packages (DONE)](#markdown-header-step2-manage-license-packages-done)
  - [Step3: manage paddle prices (DONE)](#markdown-header-step3-manage-paddle-prices-done)
    - [update plan model (DONE)](#markdown-header-update-plan-model-done)
    - [manage plan (DONE)](#markdown-header-manage-plan-done)
    - [manage license package (DONE)](#markdown-header-manage-license-package-done)
  - [Step4: update subscription / invoice model to support license package](#markdown-header-step4-update-subscription-invoice-model-to-support-license-package)
  - [Step5: purchase license package - customer portal](#markdown-header-step5-purchase-license-package-customer-portal)
  - [Step6: update subscription / invoice model to support credit/items/prorate](#markdown-header-step6-update-subscription-invoice-model-to-support-credititemsprorate)
  - [Step7: manage subscription increate/decrease license number](#markdown-header-step7-manage-subscription-increatedecrease-license-number)
  - [Step8: finalize admin portal to support license package](#markdown-header-step8-finalize-admin-portal-to-support-license-package)

# License Package Feature

## Step1: refactor product/plan/coupon (DONE)

refactor product/plan/coupon to optimize product-price-discount relationship.

### Refactor Product, Plan, and Coupon Models (DONE)

   - one product -> multiple paddle product (interval-based).
   - coupon -> product restriction (interval-based).

### Migration (DONE)

  - recreate paddle products
  - recreate paddle prices
  - refresh coupons

  - archive all paddle products
  - archive all paddle prices


## Step2: manage license packages (DONE)

refactor license package model to support tiered discount.

#### license package model (DONE)

| license quantity / range | tiered discount |
| ------------------------ | --------------- |
| 1-10                     | 0%              |
| 11-20                    | 10%             |
| 21-25                    | 20%             |
| 30                       | 30%             |
| 50                       | 40%             |

Valid quantity: 1-25, 30, 50

#### migration (DONE)
  - migrate previous license package

#### refactor license package (DONE)
  - create new license package
  - update existing license package
  - info()

## Step3: manage paddle prices (DONE)

### update plan model (DONE)

update plan model to support list of paddle prices.

- add quantity based prices to meta (DONE)
- update price custom data (DONE)
- update paddle:cmd sync-price (DONE)
- update paddle:cmd sync-product (DONE)

### manage plan (DONE)

  - sync paddle prices when creating plan (DONE)
  - sync paddle prices when updating plan (DONE)
  - sync paddle prices when deleting plan (DONE)

### manage license package (DONE)

  - sync paddle prices when creating license package (DONE)
  - sync paddle prices when updating license package (DONE)
  - sync paddle prices when deleting license package (DONE)

## Step4: update subscription / invoice model to support license package

update subscription and invoice model to support new attributes (license_number, license_package_id).

  - support new attributes
    - license number
    - license package id
    - license package info
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


