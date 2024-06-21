- [Solution for LDS Pro License Sharing Requirements](#markdown-header-solution-for-lds-pro-license-sharing-requirements)
- [Overview](#markdown-header-overview)
  - [Assumptions](#markdown-header-assumptions)
  - [Concenpts](#markdown-header-concenpts)
    - [Subscription](#markdown-header-subscription)
    - [License](#markdown-header-license)
    - [Seat](#markdown-header-seat)
  - [Open issues](#markdown-header-open-issues)
- [Use Cases](#markdown-header-use-cases)
  - [Purchase and cancel subscription](#markdown-header-purchase-and-cancel-subscription)
    - [Customer purchases a subscription](#markdown-header-customer-purchases-a-subscription)
    - [Customer purchases a subscription and additional shared license package together](#markdown-header-customer-purchases-a-subscription-and-additional-shared-license-package-together)
    - [Customer purchases shared license package](#markdown-header-customer-purchases-shared-license-package)
    - [Customer upgrade shared license package (e.g. 10 -\> 20 licenses)](#markdown-header-customer-upgrade-shared-license-package-eg-10-20-licenses)
    - [Customer cancels shared license package](#markdown-header-customer-cancels-shared-license-package)
    - [Customer downgrades shared license package (e.g. 20 -\> 10 licenses)](#markdown-header-customer-downgrades-shared-license-package-eg-20-10-licenses)
    - [Customer cancels the subscription](#markdown-header-customer-cancels-the-subscription)
  - [Manage shared licenses](#markdown-header-manage-shared-licenses)
    - [View shared licenses](#markdown-header-view-shared-licenses)
    - [Invite other customers to share Licenses](#markdown-header-invite-other-customers-to-share-licenses)
    - [Cancel an invitation](#markdown-header-cancel-an-invitation)
    - [Remove a shared customer](#markdown-header-remove-a-shared-customer)
    - [Reorder invitations](#markdown-header-reorder-invitations)
  - [Using Shared Licenses (for shared customers)](#markdown-header-using-shared-licenses-for-shared-customers)
    - [Shared License Seats](#markdown-header-shared-license-seats)
    - [Viewing Shared Licenses](#markdown-header-viewing-shared-licenses)
    - [Accepting a shared license invitation](#markdown-header-accepting-a-shared-license-invitation)
    - [Reject a shared icense invitation](#markdown-header-reject-a-shared-icense-invitation)
    - [Cancel an accepted shared license](#markdown-header-cancel-an-accepted-shared-license)
    - [Using shard license](#markdown-header-using-shard-license)
- [Project plan](#markdown-header-project-plan)
  - [First phase: Standard shared license support](#markdown-header-first-phase-standard-shared-license-support)
  - [Next phase: EDU shared license support](#markdown-header-next-phase-edu-shared-license-support)

# Solution for LDS Pro License Sharing Requirements
# Overview
## Assumptions

- Only the Pro License can be shared; a basic License cannot be shared.
- Customers can purchase shared licenses either when they subscribe or afterward.
- Only shared licenses can be shared; the Pro License included with the subscription purchase cannot be shared.
- Purchasing and upgrading of shared licenses will take effect immediately. However, cancellation and downgrading will take effect from the next period. 

## Concenpts

### Subscription

- Subscription is the product (plan) instance that owned by customers (either purchased or assigned by system).
- Subscription may have differet level: basic, pro
- Subscription may have different period: monthly, annually
- Subscription may have free trial period, after free trial period, the subscription will be charged (monthly or annually).
- One customer can own zero or one Subscription at a time.
- When a customer purchases a cutter, he will own a basic subscription.
- When a customer purchases a Pro subscription, he will own a Pro subscription.
- When a customer cancel his Pro subscription, he will own a basic subscription (if he have purchased a cutter) or no subscription (if he have not purchased a cutter).
- When a customer own a basic subscription and transfer all his cutters to other customers, he will own no subscription.

### License

- License is the permission for customers to use the LDS software.
- License has two levels: basic, pro
- License has two types: build-in (can be basic or pro), shared (alway pro)
- Build-in license
  - Build-in license the license that comes with the subscription and can not be shared.
  - Build-in license can only be used by subscription owner.
- Shared license
  - Shared license is the license that purchased by customers for sharing.
  - Shared license can only be purchased by pro subscription owner.
  - Shared license can be shared with other customers. Once shared, it can not be used by the owner.
  - Shared lincense can only be shared with customers who do not have a pro license.
- license ownership:
  - One customer can own one basic license, or
  - One customer can own one build-in pro license, or
  - Onc customer can own one build-in pro license and multiple shared pro license.
- license using:
  - License-owners can use owned license if they are not shared with other customers.
  - Non license-owners can use license that is shared with them.

### Seat

- Seats are number of Lds software instances one license user can run simultaneously.
- Seats are attached to licences.
- For basic license, the number of seats is 3 * (number of owned cutters).
- For build-in pro license, the number of seats is 3 or 3 * (number of owned cutters), whichever is greater.
- For shared pro license, the number of seats is 3.

- number of seats for customer:
  - For subscripton owner: (number of seats of all owned licenses) - (number of seats of all license shared with others).
  - For customer using shared license: 3.

## Open issues
- Shared license package type
  - EDU shared packages
    - Great discount
    - Billing email address must be certified. must be a edu domain.
    - Can only be shared with same domain customers.
  - Standard shared packages
    - Less discount
    - Flexibility of sharing
- Shared license package price
  - number of seats
  - discount (compared to standard price)
- Can customer buy additional shared licenses together with a pro subscirption with free trial discount?
- Refund policy for shared license package need to be defined.

# Use Cases

## Purchase and cancel subscription

### Customer purchases a subscription

- This is the default case.

### Customer purchases a subscription and additional shared license package together

- Shared license pcakge is always bound to a pro subscription. 
- Customer can own maximal 1 shared license packages at a time.
- The subscription's items include the subscription itself and the shared license package.
- Additional agreement is required to display when purchasing shared license packages.
  
Some recommendations:  
- the shared pro license share the same discount (percentage and period) as the Pro Subscription
- the price of the shared Pro license is set as a percentage of the Pro Subscription price. This approach eliminates the need to configure prices for individual packages and currencies..

### Customer purchases shared license package

- Only owner of pro subscription can purchase shared licenses.
- This is an once off checkout procudure. The customer shall agree that:
  - He will be charged for the cost of the shared license package from today to the end date of current subscription period
  - The subscription he owned will be updated to include the additional shared license package from next period.
- Customers will be charged for Pro Subscription + N shared licenses from the next period.

### Customer upgrade shared license package (e.g. 10 -> 20 licenses)

- Similar to the previous case.
- Customer will be charged for the difference between the current shared license package and the new shared license package for current period.

### Customer cancels shared license package

- Customers can cancel their License package.
- Cancellation takes effect from the next period.
- The susbcription will be updated to remove the shared licenses from the next period.
- Shared Licenses remain active until the end of the current period.
- If the package is eligible for refund, a refund will be issued. (TBD)

### Customer downgrades shared license package (e.g. 20 -> 10 licenses)

- Downgrade takes effect from the next period.
- The susbcription will be updated to reflect the changes of shared license package from the next period.
- Shared Licenses number remains until the end of the current period.

### Customer cancels the subscription

- When customers cancel the subscription, the shared license package are also canceled.
- If the subscription and/or shared license package is eligible for refund, a refund will be issued.

## Manage shared licenses

### View shared licenses
- Customers can view total number of shared licenses.
- Customers can view all invitations (open, expired, active, rejected and cancelled)

### Invite other customers to share Licenses
- Customers can invite other customers to share Licenses.
- Only registered customers without a Pro License can be invited.
- The sum of active + active invitations cannot exceed the number of shared licenses.

### Cancel an invitation
- Customers can cancel an active or active invitation.

### Remove a shared customer
- Customers can remove an invitation.

### Reorder invitations
- Customers can reorder open and active invatations.
- If the number of shared licenses decreases, the invitations at the end of list will be cancelled. Customers may rearrange the order of invitations.

## Using Shared Licenses (for shared customers)

- One customer can be assigned one shared license only.

### Shared License Seats

- The number of seats for shared customers is always 3, even if they purchase cutters.

### Viewing Shared Licenses

- Shared customers can view shared license information on the subscription page, including the following information:
  - Product type (Pro License).
  - License sharer.
- Shared customers can perform the following operations on the subscription page:
  - Reject an invitation.
  - Accept an invitation.
  - Cancel an accetped invitation.

### Accepting a shared license invitation

- Customers without a Pro License can receive and accept an invitation.
- Once accepted, customers can use the shared license as if they had purchased it.

### Reject a shared icense invitation

- Customers can refuse the invitation.

### Cancel an accepted shared license 

- Customers can cancel an accepted invitation shared by others.
- Once canceled, customers cannot use the shared license.
- Customers must cancel the shared license before purchasing their own Pro License.

### Using shard license

- Customer can use shared license in LdsSoftware the same way as they purchsing a subscription

# Project plan

## First phase: Standard shared license support

- Shared license can be purchased by any customer
- License owner can share license with any customers without a Pro License

## Next phase: EDU shared license support

- Shared license can only be purchased by certified EDU customers
- Shared license can only be shared with certified EDU customers and with the same EDU domain email address


