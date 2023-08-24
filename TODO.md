

Use case:
+ subscription
  + cancel subscription
    + if refundable & apply refund, refund request, cancel & terminate subscription (OK)
    + if not refundable, cancel subscription (OK)

+ order
  + view order
    + order status (refunding, refunded, refund-failed, refund-partly) (OK)
  + view order's refunds (OK)
  + create refund (admin only) (OK)
    + no restriction (OK)

+ refund 
  + view refund (NOK)

+ notification
  + refund failed
  + refund success


Design:
+ migration & model
  + refund table
  + refund model
  + invoice status

+ refund service
  + is order refundable
  + refund order

+ refund controller
  + list

+ digitalservice.php
  + create refund
  + get refund
  + list refund

+ managementDr
  + refund order
  + refund event
    + refund requested
    + refund failed
    + refund success

+ notification
  + ...
  + 

credit-memo