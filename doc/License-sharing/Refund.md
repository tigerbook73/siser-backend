# Refund Manual Test

Cases:

<!-- standard subscription -->
+ create subscription & license package
+ cancel subscription
  + invoice refunded
+ completed
  
+ create subscription & license package
+ cancel license package
  + license item refunded
+ cancel subscription
  + invoice refunded
+ completed

+ create subscription & license package
+ cancel license package
  + license item refunded
+ buy new license package
+ cancel subscription
  + subscription invoice refunded
+ completed

+ create subscription & license package
+ cancel license package
  + license item refunded
+ buy new license package
+ cancel license package
  + not refundable
+ buy new license package
+ cancel subscription
  + subscription invoice refunded
+ completed

+ create subscription & license package
+ increase license number
+ increase license number
+ cancel subscription
  + subscription invoice refunded
  + license package invoice refunded
  + license package invoice refunded
+ completed

+ create subscription & license package
+ increase license number
+ increase license number
+ cancel license package
  + license item refunded
  + license package invoice refunded
  + license package invoice refunded
+ completed

+ create subscription & license package
+ partial refund from admin portal
+ cancel subscription
  + no refund
+ completed
  
+ create subscription & license package
+ partial refund from admin portal
+ cancel license package
  + no refund
+ cancel subscription
  + no refund
+ completed

+ create subscription
+ buy license package
+ cancel subscription
  + subscription invoice refunded
  + license invoice refunded

+ create subscription
+ buy license package
+ cancel license package
  + license item refunded
+ cancel subscription
  + subscription invoice refunded
+ completed

+ create subscription
+ buy license package
+ increase license number
+ increase license number
+ cancel subscription
  + subscription invoice refunded
  + license package invoice (buy) refunded
  + license package invoice (increase) refunded
  + license package invoice (increase) refunded

+ create subscription
+ buy license package
+ increase license number
+ increase license number
+ cancel license package
  + license package invoice (buy) refunded
  + license package invoice (increase) refunded
  + license package invoice (increase) refunded
+ cancel subscription
  + subscription invoice refunded

<!-- free trial -->
+ create subscription & license package
+ cancel subscription
  + no refunded
+ completed
  
+ create subscription & license package
+ cancel license package
  + no refunded
+ cancel subscription
  + no refunded
+ completed

+ create subscription & license package
+ cancel license package
  + no refunded
+ buy new license package
+ cancel subscription
  + no refunded
+ completed

+ create subscription & license package
+ cancel license package
  + no refunded
+ buy new license package
+ cancel license package
  + no refunded
+ buy new license package
+ cancel subscription
  + no refunded
+ completed

+ create subscription & license package
+ increase license number
+ increase license number
+ cancel subscription
  + no refunded
+ completed

+ create subscription & license package
+ increase license number
+ increase license number
+ cancel license package
  + no refunded
+ completed

+ create subscription
+ buy license package
+ cancel subscription
  + no refunded

+ create subscription
+ buy license package
+ cancel license package
  + no refunded
+ cancel subscription
  + no refunded
+ completed

+ create subscription
+ buy license package
+ increase license number
+ increase license number
+ cancel subscription
  + no refunded

+ create subscription
+ buy license package
+ increase license number
+ increase license number
+ cancel license package
  + no refunded
+ cancel subscription
  + no refunded
