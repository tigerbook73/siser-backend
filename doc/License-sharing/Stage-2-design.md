## Purchase License Package separately

## Use cases
+ Customer purchase a license package
  + Pre-condition
    + customer has an active pro subscription (not cancelling)
    + customer has no active license package
    + customer has no pending order
    + customer has no pending invoice
  + Place order
    + For free-trial period
      + Customer choose his license package & number
      + Price = 0
    + If not in free-trail period
      + Customer choose his license package & number
      + Discount: use current subscription's coupon
      + Price: from today to the end of current period
    + create order and checkout
      + create once-off order
      + use current payment method by default
      + if customer choose a new payment method, the existing payment method of current subscription will be replaced
      + order must be converted succssfully and in 'accepted' status.
  + onOrderFailed
    + update order
    + do nothing with subscription
  + onOrderAccepted
    + update subscription (license-package-info, items)
    + create license-sharing (license-sharing object)
    + update dr-subscription (next period)
  + onOrderChargeCatpureFailed
    + update order
    + reverse subscription
  + onOrderComplete
    + update order
  + checkout other event as well
    + ...

+ Customer cancel license package
  + Pre-condition
    + customer has an active license package for next period
    + customer has no pending order
  + Cancel
    + send notification to customer
    + update subscription (license-package-info, items)
    + update dr-subscription (next period)

+ Customer increase license number when
  + Pre-condition
    + customer has an active pro subscription (not cancelling)
    + customer has an active license package (even if it is cancelling/decreasing)
    + customer has no pending order
  + Purchase
    + Customer choose his license package & number (only incresing)
    + Discount: use current subscription's coupon
    + Price: from today to the end of current period (only for increasing number)
  + onOrderFailed
    + update order
    + do nothing with subscription
  + onOrderAccepted
    + update subscription (license-package-info, items)
    + update dr-subscription (next period)
  + onOrderChargeCatpureFailed
    + update order
    + reverse subscription
  + onOrderComplete
    + update order

+ Customer decreate license number when
  + Pre-condition
    + customer has an active license package for next period and number > 1
    + customer has no pending order
  + Decrease
    + send notification to customer
    + update subscription (license-package-info, items)
    + update dr-subscription (next period)

+ Subscription Extended
  + Move to next period
  + Refresh license-sharing
+ Subscription Failed
  + Refresh license-sharing (already completed in previous steps)


## Design

### API

+ Customer API
  + LicensePackageOrder
    + create new license package order
    + create increase license number order
    + pay order
    + cancel order
    + list orders
    + get order

  + Account/LicensePackage
    + cancel license package
    + decrease license number

+ Admin API
  + LicensePackageOrder
    + list orders
    + get order
    + cancel order

### Webhook

+ LicensePackageOrder
  + onOrderAccepted
    + update subscription
    + 
  + onOrderFailed
  + onOrderChargeCatpureFailed
  + onOrderComplete

### Service

+ createOrder
+ deleteOrder
+ payOrder
+ cancelOrder
+ fullFillOrder
+ refundOrder
+ 
+ addLicensePackage (also next)
+ cancelLicensePackage (next only)
+ increaseLicenseNumber (current and next)
+ decreaseLicenseNumber (next only)
+ 
+ stopLicensePackage (current and next)
+ 



## Progress

### rest-api


1. add invoice.type 
2. list invoice by type
3. create invoice
4. pay invoice
5. add InvoiceDR.checkout_payment_session_id

6. create license-package order
7. add productItemCatetory.license-prorated


## frontend
1. remove order_only OK

## frontend-admin


## backend


### TODO:
+ remove order-only from invoice filter


### Notification
+ order confirmed
+ order aborted
+ order refunded
+ license package cancelled
+ licsnse package decreased
+ license package increased
+ license package stopped
