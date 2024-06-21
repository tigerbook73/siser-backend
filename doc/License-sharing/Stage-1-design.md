
### Manage LicensePackage (OK)
  + Use cases
    + Admin can create a new license package /OK
    + Admin can update a license package (includes deactivating) /OK
    + Admin can delete a license package /OK
    + Admin can list all license packages /OK
    + Customer can list all eligible license packages /OK?
  + Migrations & Models
    + migrations /OK
    + models /OK
  + BE: List LicensePackage
    + REST API /OK
    + controller /OK
  + BE: Create LicensePackage
    + REST API /OK
    + controller /OK
  + BE: Update License Package
    + REST API /OK
    + controller /OK
  + BE: Delete License Package
    + REST API /OK
    + controller /OK
  + FE-Admin: Manage LicensePackage
    + List page /OK
    + create page /OK
    + update page /OK
    + delete page /OK

### Manage Shared Licence (invation)
  + Use cases
    + Owner customer can view all invitations /OK
    + Owner customer can create an invitation /OK
    + Owner customer can update an invitation /OK
    + Owner customer can cancel an invitation /OK
    + Owner customer can delete an invitation /OK
    <!-- + TODO:  -->
    + Guest customer can get notificated /OK
    + Guest customer can accept an invitation /OK
    + Guest customer can cancel an invitation /OK
    + Admin can view customer's invitations
    + Open invitations will be expired after a certain period of time /OK
    + Open and active invitations may be cancelled if shared license number is updated /OK
  + Migrations & Models
    + migrations (invitation, user, subscription) /OK
    + models /OK
  + BE: update shared license number
    + update subscription's shared license number /OK
    + update user's shared license number /OK
    + cancel sharing/inviatation if necessary /OK
  + BE: List Invitation (Owner Customer)
    + REST API /OK
    + controller /OK
  + BE: Create/Send Invitation (Owner Customer)
    + REST API /OK
    + controller /OK
  + BE: Delete Invitation (Owner Customer)
    + REST API /OK
    + controller /OK
  + BE: Expire Invitation (Crons tasks)
    + cron task /OK
  + BE: Accept Invitation (Guest Customer)
    + REST API /OK
    + controller /OK
  + BE: Reject Invitation (Guest Customer)
    + REST API /OK
    + controller /OK
  + BE: Update user subscription level
    + update user subscription level /OK
    + update LdsLicense /OK
  + BE: notification
    + invitation notification /OK
    + invitation accepted /OK
    + invitation cancelled /OK
  + FE-Admin: Subscription Page (later)
    + Standard + LicensePackage 
    + Shared License
  + FE-Admin: Shared license/invitation page (later)
    + Invitation list
  + FE-Customer: Invitation Page (Owner Customer)
    + create invitation
    + cancel invitation
    + remove invitation
    + list invitation
  + FE-Customer: Invitation Page (Guest Customer)
    + accept invitation
    + cancel invitation
    + remove invitation
    + list invitation

### Purchase Subscription together with LicensePackage (cancel/stop)
  + User cases
    + Customer can purchase a subscription together with a license package /OK
    + Customer can cancel a subscription (licence package will be cancelled as well) /OK
    + Customer can view his subscription and license package /OK
    + Admin can view customer's subscription and license package
    + Admin can cancel customer's subscription and license package
  + REST APIs
    + create subscription  /POK
    + subscription /POK
    + invoice /POK
  + Migration & Models:
    + migrations (subscriptions, invoices) /POK
    + models (subscriptions, invoices) /POK
  + BE service:
    + create subscription (with license package) /OK
    + pay subscription (with license package) /OK
    + cancel subsription (with license package) /OK
  + BE service: event
    + order.accpeted event /OK
    + order.complete event /OK
    + order.failed/cancell event (all) /OK
    + subscription.extended event /OK
    + check all other events
  + BE controller:
    + create subscription /OK
    + pay subscription /OK
    + cancel subscription /OK
    + list subscription /OK
    + get subscription /OK
    + list/get invoices /OK
    + refund orders /OK
  + BE: notification
    + check all order & subscription event
      + add (optional) items 
  + FE-Admin: Subscription Page
    + show subscription (with license package)
    + cancel subscription
  + FE-Admin: Invoice Page
    + list invoices
    + cancel invoices
    + refund invoices
  + FE-Customer: Subscription Page
    + show subscription (with license package) /OK
    + cancel subscription /OK
  + FE-Customer: Invoice Page
    + invoices page /OK
  + new subscription steps:
    + select license package steps /OK
    + all order summary information /OK
    + cancel subscription
  + FE-Customer shopping steps
    + Select license package steps /OK
    + Order summary component /OK
  + REST API
    + subscriptions /OK
    + invoices /OK


Issues: 
+ License package price policy
+ Different LDS products need different License Package? E.g. LDS-Pro-20, LDS-Premium-20
  + In the future, if customer need to upgrade there subscription, the license package should be upgraded as well.


Issue:
+ keep license_count for a while
