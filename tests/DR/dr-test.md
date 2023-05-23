# Subscription

## Test Scenarios

```mermaid
stateDiagram-v2
  [*]
  [*]                       --> BillingInfo               : update billing info

  BillingInfo
  BillingInfo               --> PaymentMethod             : update payment method

  PaymentMethod
  PaymentMethod             --> Draft                     : create subscription

  
  Draft
  Draft                     --> Pending                   : pay subscription
  Draft                     --> [*]                       : timeout|delete / destory
  
  Pending
  Pending                   --> Processing                : order.accepted
  Pending                   --> Failed                    : payment.failed

  Processing
  Processing                --> Active                    : order.complete
  Processing                --> Failed                    : payment.failed 

  state Active {
  [*]                       --> Active.Normal
  state Active.Normal {
  [*]                       --> Active.Invoice.Completing
  Active.Invoice.Completing
  Active.Invoice.Completing --> Active.Invoice.Completed  : order.invoice.created

  Active.Invoice.Completed
  Active.Invoice.Completed  --> Active.Invoice.Open       : invoice.open
  Active.Invoice.Open
  
  Active.Invoice.Open       --> Active.Invoice.Pending    : subscription.payment.failed
  Active.Invoice.Open       --> Active.Invoice.Completing : subscription.extended
  
  Active.Invoice.Pending
  Active.Invoice.Pending    --> Active.Invoice.Completing : subscription.extended
  }
  Active.Normal
  Active.Normal             --> Active.Cancelling         : cancel subscription

  Active.Cancelling
  Active.Cancelling         --> Active.Cancelling         : order.invoice.created
  }

  Active.Invoice.Pending    --> Failed                    : subscription.failed
  Active.Cancelling         --> Stopped                   : subscription cancelling expired

  Failed
  Failed                    --> [*]

  Stopped
  Stopped                   --> [*]
```




