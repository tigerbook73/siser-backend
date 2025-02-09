Product Design:

Product
+ one product -> multiple paddle product (interver based)
+ product
  + product->meta->paddle->product_id (monthly)
  + product->meta->paddle->product_month_id
  + product->meta->paddle->product_year_id
  + product->meta->paddle->product_2day_id

+ paddle product
  + paddleProduct->customData->product_id
  + paddleProduct->customData->interval {month, year, 2day}

Plan
+ no changes

CreatePaddleProduct
+ create 3 paddle product from one product

Coupon:
+ restriction:
  + month -> monty product
  + year -> year product
  + 2day -> 2day product

+ longterm
  + no restriction

+ remove restriction
  + to plans
  +
