Release.md

# publis operations

1. ./refresh.sh staging / production
2. art paddle:cmd sync-products
3. art paddle:cmd sync-prices
4. art publish:cmd validate-subscriptions
5. art publish:cmd validate-invoices
6. art publish:cmd refresh-subscriptions
7. art publish:cmd refresh-invoices


A pre-release version of the bulk license package has been deployed on the staging site.

### Main Features:  
- Purchasing a plan with a bulk license is now supported.  
- Coupon codes are now only applicable on our own page.  
- Customers who own a plan with a bulk license can share their licenses with other users who do not have a Pro license.  

**Note:** Increasing or decreasing the number of licenses is currently unavailable and will be implemented soon.  

### For Customers:  
- When purchasing a plan, customers can choose between a single license (default) or a bulk license (2+).  
- The product item in the invoice will be displayed as follows:  
  - **Leonardo® Design Studio Pro Monthly Plan** – for a single license  
  - **Leonardo® Design Studio Pro Monthly Plan (License x 3)** – for a bulk license  
- When a customer purchases a plan with a bulk license, the **"License Packages"** menu will appear in the sidebar. The customer can then share their licenses with other users.  
  - **Note:** Licenses built-in from a purchased machine are not shareable.  
- Bulk licenses are not eligible for free trials.  

### For Admins:  
- The **"License Package"** object on the **"License Packages"** page must be activated before customers can purchase bulk licenses.  
- Not all quantities within the price tiers are purchasable — only the specified quantities can be purchased.  
- When updating plans or license packages, changes may take some time to take effect.  
