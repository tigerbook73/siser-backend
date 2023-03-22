
Backend

+ DR configuration (env/config)
+ DR init data (plan, sku group)
+ DR service: API (customer / source, checkout, order, subscription, invoice)
+ DR service: Webhook
+ DR service (mockup: API / Webhook)
+ SubscriptionManager
+ SubscriptionManager (mockup)
+ Customer Notification
  + Order Accpeted
  + Order Confirmed
  + Invoice Pdf
  + Subscription Terminated
  + Subscription Extended
  + Subscription Reminder
  + Subscription Cancelled
  + Subscription Failed
  + Subscription Overdue
  + Subscription Update (Coupon Expired)
  + Charge Back
+ Schedule Task
  + Validate subscription status
+ Important Flow
  + Update billingInfo
  + Update paymentMethod
  + Update plan
  + Update/Delete country
  + Update coupon
  + Update setting
  + Update machine (remove/create)
+ Sale
  + Sales Data

+ Additional
  + Delete payment method (no subscription)

+ MailBlade
  + Order Accpeted
  + Order Confirmed
  + Invoice Pdf
  + Subscription Terminated
  + Subscription Extended
  + Subscription Reminder
  + Subscription Cancelled
  + Subscription Failed
  + Subscription Overdue
  + Subscription Update (Coupon Expired)

UI:
+ additional validation
  + Update billingInfo (country & state & postcode)
  + ...
+ i18n
+ locale for digital-river
+ Sales Summary
+ 