
- [Pre-release of Siser Software Store](#markdown-header-pre-release-of-siser-software-store)
- [Introduction](#markdown-header-introduction)
  - [Site information](#markdown-header-site-information)
    - [Staging](#markdown-header-staging)
    - [Production](#markdown-header-production)
  - [Supported payment method](#markdown-header-supported-payment-method)
  - [Supported Countries and Languages](#markdown-header-supported-countries-and-languages)
    - [Support countries](#markdown-header-support-countries)
    - [Supported currencies](#markdown-header-supported-currencies)
    - [Supported languages (TODO: not released yet)](#markdown-header-supported-languages-todo-not-released-yet)
  - [Subscripiton Plan](#markdown-header-subscripiton-plan)
    - [UI's language](#markdown-header-uis-language)
  - [Trusted customer](#markdown-header-trusted-customer)
  - [Test Issues](#markdown-header-test-issues)
  - [Information to Know](#markdown-header-information-to-know)
- [Site \& URL](#markdown-header-site-url)
- [Acceptance test cases](#markdown-header-acceptance-test-cases)
  - [Access Cart Page](#markdown-header-access-cart-page)
  - [Tax ID](#markdown-header-tax-id)
  - [Address Auto-complete and Validation](#markdown-header-address-auto-complete-and-validation)
  - [Challenge](#markdown-header-challenge)
  - [Place order successfully](#markdown-header-place-order-successfully)
  - [Place order unsuccessfully](#markdown-header-place-order-unsuccessfully)
  - [Order confirmed](#markdown-header-order-confirmed)
  - [Order Invoice Generated](#markdown-header-order-invoice-generated)
  - [Order Failed](#markdown-header-order-failed)
  - [Cancel Order](#markdown-header-cancel-order)
  - [Cancel Subscription without choose refund](#markdown-header-cancel-subscription-without-choose-refund)
  - [Cancel Subscription without after 14 days of purchasing](#markdown-header-cancel-subscription-without-after-14-days-of-purchasing)
  - [Cancel Subscription with Refund (within 14 days of purchase)](#markdown-header-cancel-subscription-with-refund-within-14-days-of-purchase)
  - [Refund Order](#markdown-header-refund-order)
  - [Refunded Notifcation](#markdown-header-refunded-notifcation)
  - [Order credit-memo Generated](#markdown-header-order-credit-memo-generated)
  - [Refund Failed](#markdown-header-refund-failed)
  - [Cancel Subscritpion](#markdown-header-cancel-subscritpion)
  - [Support Billing Language](#markdown-header-support-billing-language)
  - [Supported Billing Locale](#markdown-header-supported-billing-locale)


# Pre-release of Siser Software Store

# Introduction

This is document is used to guide the acceptance test of Siser Software Store.

## Site information

### Staging
+ customer portal:  https://software.leonardodesignstudio.com
+ admin portal:     https://admin.software.leonardodesignstudio.com

Test payment for staging site can be found in the following link:
+ https://docs.digitalriver.com/digital-river-api/developer-resources/testing-scenarios#testing-standard-payment-methods

### Production
+ customer portal:  https://software.siser.com
+ admin portal:     https://admin.software.siser.com

## Supported payment method

Only the following payment methods are supported:

+ PayPal Billing
+ Credit Card
+ Google Pay


## Supported Countries and Languages

### Support countries

There are so many countries. Please visit:

1. customer portal: BillingInfo Page / country dropdown for the full list.
2. admin portal: https://admin.software.siser.com/admin/countries

### Supported currencies

Each country has a predefined currency. Please see the following link for countries and their currencies:

admin portal: https://admin.software.siser.com/admin/countries

### Supported languages (TODO: not released yet)

Update to now, multiple languages support is desgined but not released.

There are two type of languages:
1. UI languages: it will only affect the webpages
2. Billing languages: it will affect the email notification and invoice

They will finally be merged into one.
1. User must choose country and language.
2. User's billing address country must same as the choosed country.
3. The web page, email notification and invoice will use the same languages.

## Subscripiton Plan

There are only montyly plan for now.

Plan name must include "Monthly or "Yearly" to indicate the billing cycle.

Subscription Renew Logic:
1. send reminder to customer 7 days before the end of current billing cycle.
2. charge customer 3 days before the end of current billing cycle.
3. if charge failed, send email to customer and rety until 10 days after the end of current billing cycle.
4. if sucessfully charged, subscription will remain active.

###  UI's language

The following languages are planned to supported. But are not ready for now.

en: English
fr: French
it-Italian
de: German
es: Spanish


## Trusted customer

Email address in the following domain will be view as trusted customers and can access online-store pages.

+ fcl.software
+ iifuture.com
+ siser.com
+ siseranz.com
+ siserasia.com
+ siserna.com

TODO:

+ only allow trusted customer to use the system
+ send trused customer email to lizzie

The trusted customer information is only valid during pre-release period. After that, the trusted customer information will be removed.

## Test Issues

## Information to Know




# Site & URL



# Acceptance test cases

## Access Cart Page

URL: https://software.siser.com/cart

+ T: user must sign in before they can access cart page
+ T: User with a machine can access cart page
+ T: User without a machine can access cart page

## Tax ID

Note: not applicable for US customers

+ T: business customer may have tax id (e.g. ABN for Australia)
+ T: it is optional for business customer to provide tax id

## Address Auto-complete and Validation

Address autocomplete does not work in all countries. In some country, it is not working properly, e.g. do not fill city.  -- efforts need to correct these issues.


Address validation is turned off for now. -- there are many issues to fix because too many countries listed.

Please note: for certain country, state & postcode must be correct because they are used to calculate tax.

## Challenge
  + How to test so many countries?
  + How to test so many currencies?
  + How to test so many languages?

## Place order successfully
  + Action
    + successfully place order 
  + Result
    + see complete step
    + order is created, status = pending / processing

## Place order unsuccessfully
  + Action
    + input wrong information and does not goto the complete step.
  + Result
    + site shall prompt user in the following situation:
      + billing information is not complete
      + tax id is not invaid
      + address is not valid (e.g. state / postcode is not correct in USA)
      + payment method is invalid (invalid card number, card expired etc.)
      + payment charge failed (maybe)

## Order confirmed
  + Action
    + Order is completed by DR. Usually a few seconds after order is placed.
  + Result
    + Subscription is activated
    + Order is updated, status = completed
    + Notification: order confirmed email
    + LDS software can check-in/out and use the pro features
  
## Order Invoice Generated
  + Action
    + Invoice PDF is generated by DR. It may take up to hours to generate.
  + Result
    + Notification: order invoice email
    + User can download invoice from customer portal

## Order Failed
  + Action
    + Order is failed by DR. Usually because of payment failure.
  + Result
    + No subscription activated
    + Order is upodated to "failed"

## Cancel Order
  + Action
    + Only when order is in pending status. Usually the payment is pending for review.
    + User can order from order page
  + Result
    + Order is cancelled, if successfully, there will be no charge occur.
    + Notification: order cancelled email
    + Subscription not created

## Cancel Subscription without choose refund
  + Action
    + Cancel subscription without choosing refund options
  + Result
    + Notification: subscription cancelled email (with some order refund information)

## Cancel Subscription without after 14 days of purchasing
  + Action
    + Cancel subscription
  + Result
    + Notification: subscription cancelled email

## Cancel Subscription with Refund (within 14 days of purchase)
  + Action
    + Cancel order within 14 days and choose the refund option
  + Result
    + Subscripiton is terminated immediately
    + Notification: subscription cancelled email (with order refund information)

## Refund Order
  + Action
    + Order is completed, and not in "refunding" or "refunded" status
    + Refund order from admin portal
  + Result
    + Order status = "refunding"

## Refunded Notifcation
  + Action
    + Order refund is processed by DR (request may be initiated by admin or customer)
  + Result
    + Notification: order refunded email
    + order updated: status = refunded, or partially-refunded (depends on the refund amount)

## Order credit-memo Generated
  + Action
    + Credit-memo is generated by DR. It may take up to hours to generate.
  + Result
    + Notification: order credit-memo email
    + User can download credit-memo from customer portal

## Refund Failed
  + Action
    + Order refund can not be processed
  + Result
    + Notification: order refund-failed email
    + order updated: status = refund-failed

## Cancel Subscritpion




## Support Billing Language

en: English
fr: French
it-Italian
de: German
es: Spanish

## Supported Billing Locale

The following is a list of supported local for billing.


| Locale | Language | Country       |
| ------ | -------- | ------------- |
| fr_BE  | French   | Belgium       |
| fr_CH  | French   | Switzerland   |
| fr_FR  | French   | France        |
| fr_CA  | French   | Canada        |
| it_CH  | Italian  | Switzerland   |
| it_IT  | Italian  | Italy         |
| de_AT  | German   | Austria       |
| de_CH  | German   | Switzerland   |
| de_DE  | German   | Germany       |
| es_ES  | Spanish  | Spain         |
| en_US  | English  | United States |
| en_US  | English  | Others        |

Please note that:
1. For countries that are not listed above, only english are suppored.
2. For countries that are listed above, English are also supported.

