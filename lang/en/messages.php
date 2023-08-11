<?php

return [
  // english
  'order.status.pending'                          => 'Pending',
  'order.status.completing'                       => 'Confirmed',
  'order.status.completed'                        => 'Completed',
  'order.status.cancelled'                        => 'Cancelled',
  'order.status.void'                             => 'Cancelled',
  'order.status.failed'                           => 'Failed',
  'order.status.refunding'                        => 'Refunded',
  'order.status.refunded'                         => 'Refunded',

  'order.type'                                    => 'Order Type',

  'payment_method'                                => 'Payment Method',
  'payment_method.creditCard'                     => 'Credit Card',
  'payment_method.payPalBilling'                  => 'PayPal Billing',
  'payment_method.googlePay'                      => 'Google Pay',

  'credit_card.brand'                             => 'Brand: :brand',
  'credit_card.card_no'                           => 'Card No: ####-####-####-:last_four_digits',
  'credit_card.expire_at'                         => 'Expires At: :month/:year',

  'order.#'                                       => 'Order # :order_id',
  'order.no'                                      => 'Order No',
  'order.date'                                    => 'Order Date',
  'order.status'                                  => 'Order Status',

  'customer_info'                                 => 'Customer Info',
  'billing_address'                               => 'Billing Address',

  'order_items'                                   => 'Items (all prices are in :currency)',
  'order_item'                                    => 'Item',
  'order_quantity'                                => 'Quantity',
  'order_price_excl'                              => 'Price (Excl. :tax)',

  'order_subtotal'                                => 'Subtotal (Excl. :tax)',
  'order_total'                                   => 'Grand Total (Incl. :tax)',
  'order_refunded'                                => 'Refund Total (Incl. :tax)',

  'subscription_info'                             => 'Subscription Info',
  'subscription_no'                               => 'Subscription No.',
  'subscription.plan_name'                        => 'Plan Name',
  'subscription.billing_period'                   => 'Billing Period',
  'subscription.billing_period.monthly'           => 'Monthly',
  'subscription.billing_period.monthly_trial'     => 'Monthly after Free Trial',
  'subscription.billing_period.annually'          => 'Annually',
  'subscription.billing_period.annually_trial'    => 'Annually after Free Trial',
  'subscription.price'                            => 'Price (Excl. :tax)',
  'subscription.subtotal'                         => 'Sub Total (Excl. :tax)',
  'subscription.tax'                              => ':tax',
  'subscription.total_amount'                     => 'Grand Total (Incl :tax)',
  'subscription.currency'                         => 'Currency',
  'subscription.start_date'                       => 'Subscription Start Date',
  'subscription.end_date'                         => 'Subscription End Date',
  'subscription.period'                           => 'Current Period',
  'subscription.period_start_date'                => 'Current Period Start Date',
  'subscription.period_end_date'                  => 'Current Period End Date',
  'subscription.period_free_trial'                => 'Free Trial Period',
  'subscription.next_invoice_date'                => 'Next Invoice Date',
  'subscription.next_invoice_total_amount'        => 'Next Invoice Grand Total (Incl :tax)',

  'order_confirm.notification'                    => 'We are pleased to inform you that your order to the <b>:plan_name</b> subscription has been confirmed!',
  'order_confirm.summary'                         => 'Here is a summary of your order and subscription:',
  'order_confirm.agreement_claim'                 => 'You have agreed to the subscription terms unless you cancel your subscription.',

  'layout.greeting'                               => 'Dear :name,',
  'layout.manage_subscription'                    => 'You can view and/or manage your subscriptions and/or orders from our :customer_portal_link.',
  'layout.faqs'                                   => 'For questions regarding cancels and refunds, please visit our :support_link.',
  'layout.contact_us'                             => 'Please contact us via email :support_email_link, or visit our :customer_support_link if you need support.',
  'layout.regards'                                => 'Kind Regards,',

  'subscription_order_abort.notification'         => 'Unfortunately, an attempted order to the <b>:plan_name</b> failed. Please check your payment method and try again.',
  'subscription_order_abort.summary'              => 'Here is a summary of the attempted order:',

  'subscription_order_cancel.notification'        => 'This is to confirm that your order to the <b>:plan_name</b> was cancelled as per your request on <b>:date</b>. You will not be charged anything.',
  'subscription_order_cancel.summary'             => 'Here is a summary of your cancelled order:',

  'subscription_order_refunded.notification'      => 'This is to confirm that your refund for order to the <b>:plan_name</b> has been confirmed. The refund amount is <b>:currency :refund_total</b>. Please note that it may take a few days for the refund to appear on your account.',
  'subscription_order_refunded.summary'           => 'Here is a summary of your refunded order:',

  'subscription_order_invoice.notification'       => 'We are pleased to provide the download link for your <a href=":invoice_pdf" download><b>invoice pdf</b></a> for your subscription to the <b>:plan_name</b>. You can also download the invoice from our :customer_portal_link.',
  'subscription_order_invoice.summary'            => 'Here is a summary of your invoice & subscription:',

  'subscription_order_credit.notification'        => 'We are pleased to provide the download link for your <a href=":invoice_pdf" download><b>credit memo</b></a> for your subscription to the <b>:plan_name</b>. You can also download the credit memo from our :customer_portal_link.',
  'subscription_order_credit.summary'             => 'Here is a summary of your invoice & subscription:',

  'subscription_reminder.notification'            => 'We would like to remind you that your subscription to <b>:plan_name</b> is scheduled to renew on or after <b>:next_invoice_date</b>.<br /><br />To ensure uninterrupted access to all your subscription benefits, please ensure your registered payment method has sufficient funds for the renewal amount.',
  'subscription_reminder.summary'                 => 'Here is a summary of your subscription:',

  'subscription_extended.notification'            => 'We are pleased to confirm that your subscription to the <b>:plan_name</b> has been successfully renewed! You can continue enjoying all the exclusive benefits and features of your subscription.<br />',
  'subscription_extended.summary'                 => 'Here is a summary of your order and subscription:',

  'subscription_cancel.notification'              => 'We’re sorry to see you go! Your <b>:plan_name</b> subscription was cancelled as per your request on <b>:date</b>. You can still access your benefits until your subscription is terminated on <b>:end_date</b>.<br /><br />Thank you for your past support, and please feel free to contact us if you have any questions or require further assistance.',
  'subscription_cancel.summary'                   => 'Here is a summary of your cancelled subscription:',

  'subscription_cancel_refund.notification'       => 'We’re sorry to see you go! Your <b>:plan_name</b> subscription was cancelled as per your request on <b>:date</b>.<br /><br />Because you choosed the refund option, your subscription was terminated immediatedly and we will process your refund request as soon as possible. Once processed, you will receive a refund confirmation email.<br /><br />Thank you for your past support, and please feel free to contact us if you have any questions or require further assistance.',
  'subscription_cancel_refund.summary'            => 'Here is a summary of your cancelled subscription:',

  'subscription_failed.notification'              => 'We are writing to inform you that the renewal charge for your <b>:plan_name</b> subscription has failed and your subscription has been terminated.<br /><br />We apologise for the inconvenience caused and request that you kindly repurchase the software if you wish to continue using the product, or contact our support team for assistance.',
  'subscription_failed.summary'                   => 'Here is a summary of your failed subscription:',

  'subscription_invoice_pending.notification'     => 'We regret to inform you that we could not process the payment for your <b>:plan_name</b> subscription<br /><br /> To prevent any disruption to your subscription access, we kindly request that you verify your registered payment method and ensure sufficient funds are available.<br /><br /> If you require any assistance or have any questions regarding your payment, please contact our support team.',
  'subscription_invoice_pending.summary'          => 'Here is a summary of your subscription:',

  'subscription_terminated.notification'          => 'We are writing to inform you that your subscription to <b>:plan_name</b> was terminated on <b>:end_date</b> as your cancellation request.',
  'subscription_terminated.summary'               => 'Here is a summary of your subscription:',

  'subscription_terms_changed.notification'       => 'We are writing to inform you that the <b>:terms</b> of your subscription to <b>:plan_name</b> was changed.<br /><br />Here is the summury of changes:<br /> :terms_items',
  'subscription_terms_changed.summary'            => 'Here is a summary of your subscription:',

  'tax_name'                                      => 'Tax',

  'coupon.coupon'                                 => 'Coupon',
  'coupon.description'                            => 'Coupon: :code (:description)'
];
