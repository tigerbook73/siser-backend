main plan x coupon


+ month plan + no ocupon (ok)
  + activated: 
    + month plan + no coupon
    + next: remain
  + cancelled:
    + refund: stopped immediately
    + no refund: stop at end of period
  + extended:
    + remain

+ month plan + free coupon (ok)
  + activate:
    + month plan + free coupon
    + next: normal plan
  + cancelled:
    + no refund: stopped immediately
  + extended:
    + normal plan

+ month plan + percentage coupon (1 month) (ok)
  + activate:
    + month plan + coupon
    + next: normal plan
  + cancelled:
    +  refund: stopped immediately
    +  no refund: stop at end of period
  + extended:
    + normal plan

+ month plan + percentage coupon (3 month) (ok)
  + activate:
    + month plan + coupon
    + next: remain
  + cancelled:
    +  refund: stopped immediately
    +  no refund: stop at end of period
  + extended:
    + remain

+ year plan + no ocupon (ok)
  + activate:
    + year plan + no coupon
    + next: month plan + no coupon
  + cancelled:
    + refund: stopped immediately
    + no refund: stop at end of period
  + extended:
    + month play + no coupon

+ year plan + free coupon (ok)
  + activated:
    + year plan + free coupon
    + next: month plan + no coupon
  + cancelled:
    + no refund: stopped immediately
  + extended:
    + month plan + no coupon

+ year plan + percentage coupon (ok)
  + activated:
    + year plan + percentage coupon
    + next: month plan + no coupon
  + cancelled:
    + refund: stopped immediately
    + no refund: stop at end of period
  + extended:
    + month plan + no coupon


1. generate coupon
2. plan coupon (month, year - inactive)
3. dr:cmd init (plan)
