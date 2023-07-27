# Invoice

## State Diagram (first invoice)

```mermaid
stateDiagram-v2
  [*]
  [*]           --> Completing      : order.completed

  Completing
  Completing    --> Completed       : order.invoice.created

  Completed
  Completed     --> [*]

```

## State Diagram (renew invoice)

```mermaid
stateDiagram-v2
  [*]
  [*]           --> Open            : invoice.open (renew)
  [*]           --> Pending         : create order

  Open
  Open          --> Failed          : subscription.failed
  Open          --> Pending         : subscription.payment_failed
  Open          --> Completing      : subscription.extended

  Pending       --> Failed          : subscription.failed
  Pending       --> Completing      : subscription.extended
  Pending       --> Processing      : order.fulfilled (first)
  
  Processing    --> Completing      : order.completed (first)

  Completing
  Completing    --> Completed       : order.invoice.created

  Completed
  Completed     --> [*]

  Failed
  Failed        --> [*]

```
