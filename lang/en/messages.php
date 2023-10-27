<?php

return [
  // english
  'order.status.pending'                          => 'Pending',
  'order.status.completed'                        => 'Completed',
  'order.status.cancelled'                        => 'Cancelled',
  'order.status.void'                             => 'Cancelled',
  'order.status.failed'                           => 'Failed',
  'order.status.refunding'                        => 'Refunded',
  'order.status.partly-refunded'                  => 'Partially Refunded',
  'order.status.refunded'                         => 'Refunded',
  'order.status.refund-failed'                    => 'Refund Failed',

  'order.type'                                    => 'Order Type',

  'payment_method'                                => 'Payment Method',
  'payment_method.creditCard'                     => 'Credit Card',
  'payment_method.payPalBilling'                  => 'PayPal Billing',
  'payment_method.googlePay'                      => 'Google Pay',

  'credit_card.brand'                             => 'Brand: :brand',
  'credit_card.card_no'                           => 'Card No: ####-####-####-:last_four_digits',
  'credit_card.expire_at'                         => 'Expires On: :month/:year',

  'order.#'                                       => 'Order # :order_id',
  'order.no'                                      => 'Order No',
  'order.date'                                    => 'Order Date',
  'order.status'                                  => 'Order Status',
  'order.total_amount'                            => 'Grand Total (Incl :tax)',
  'order.total_refunded'                          => 'Refund Total (Incl :tax)',

  'customer_info'                                 => 'Customer Info',
  'billing_address'                               => 'Billing Address',

  'order_items'                                   => 'Items (all prices are in :currency)',
  'order_item'                                    => 'Item',
  'order_quantity'                                => 'Quantity',
  'order_price_excl'                              => 'Price (Excl. :tax)',

  'order_subtotal'                                => 'Subtotal (Excl. :tax)',
  'order_total'                                   => 'Grand Total (Incl. :tax)',
  'order_refunded'                                => 'Refund Total (Incl. :tax)',

  'refund_amount'                                 => 'Amount to Refund (Incl. :tax)',
  'refund_status'                                 => 'Refund Status',

  'subscription.#'                                => 'Subscription # :subscription_id',
  'subscription.plan_name'                        => 'Plan Name',
  'subscription.billing_period'                   => 'Billing Period',
  'subscription.billing_period.count_day'         => ':interval_count Days',
  'subscription.billing_period.count_month'       => ':interval_count Month(s)',
  'subscription.billing_period.count_week'        => ':interval_count Week(s)',
  'subscription.billing_period.one_month'         => 'Monthly',
  'subscription.billing_period.one_year'          => 'Annually',
  'subscription.price'                            => 'Price (Excl. :tax)',
  'subscription.subtotal'                         => 'Subtotal (Excl. :tax)',
  'subscription.tax'                              => ':tax',
  'subscription.total_amount'                     => 'Grand Total (Incl :tax)',
  'subscription.currency'                         => 'Currency',
  'subscription.start_date'                       => 'Subscription Start Date',
  'subscription.end_date'                         => 'Subscription End Date',
  'subscription.period'                           => 'Current Period',
  'subscription.period_start_date'                => 'Current Period Start Date',
  'subscription.period_end_date'                  => 'Current Period End Date',
  'subscription.period_free_trial'                => 'Free Trial Period',
  'subscription.next_invoice_date'                => 'Next Period Invoice Date',
  'subscription.next_invoice_plan'                => 'Next Period Plan',
  'subscription.next_invoice_price'               => 'Next Period Price (Excl. :tax)',
  'subscription.next_invoice_subtotal'            => 'Next Period Subtotal (Excl. :tax)',
  'subscription.next_invoice_total_amount'        => 'Next Period Grand Total (Incl. :tax)',

  'layout.greeting'                               => 'Dear :name,',
  'layout.manage_subscription'                    => 'You can view and/or manage your subscriptions and/or orders from our :customer_portal_link.',
  'layout.faqs'                                   => 'For questions regarding cancellations and refunds, please visit our :support_link.',
  'layout.contact_us'                             => 'Please contact us via email :support_email_link, or visit our :customer_support_link if you require support.',
  'layout.regards'                                => 'Thank You,',

  'tax_name'                                      => 'Tax',

  'coupon.coupon'                                 => 'Coupon',
  'coupon.description'                            => 'Coupon: :code (:description)',
  'coupon.period_day'                             => ':interval_count days',
  'coupon.period_month'                           => ':interval_count month(s)',

  'subscription_order_abort.notification'         => 'Unfortunately, an attempted order for the <b>:plan_name</b> subscription failed. Please check your payment method and try again.',
  'subscription_order_abort.summary'              => 'Below is a summary of the attempted Order:',

  'subscription_order_cancel.notification'        => 'This is to confirm that your order for the <b>:plan_name</b> subscription was cancelled as per your request. You will not be charged.',
  'subscription_order_cancel.summary'             => 'Below is a summary of your cancelled Order:',

  'subscription_order_confirm.notification'       => 'We are pleased to inform you that your order for the <b>:plan_name</b> subscription has been confirmed!',
  'subscription_order_confirm.free_claim'         => 'The free trial plan will expire at :free_trial_end_date. The subscription will then be converted to <b>:standard_plan</b> automatically. You will be notified 7 days before the conversion and you can cancel at any time.',
  'subscription_order_confirm.manual_renewal'     => 'The annual plan will expire at :plan_end_date. In accordance with German law, it is necessary to manually renew the annual plan before it expires; otherwise, it will be terminated automatically. You will receive a notification to renew your annual plan one month prior to its expiration.',
  'subscription_order_confirm.percentage_claim'   => 'The discounted plan will expire at :coupon_end_date. The subscription will then be converted to <b>:standard_plan</b> automatically. You will be notified 7 days before the conversion and you can cancel at any time.',
  'subscription_order_confirm.summary'            => 'Here is a summary of your order and subscription:',
  'subscription_order_confirm.agreement_claim'    => 'You have agreed to the subscription terms unless you cancel your subscription.',

  'subscription_order_credit.notification'        => 'We are pleased to provide the download link to your <a href=":credit_memo" download><b>credit memo</b></a> for your <b>Order #:order_id</b> to the suscription of <b>:plan_name</b>. You can also download the credit memo from our :customer_portal_link.',
  'subscription_order_credit.summary'             => 'Below is a summary of your Order:',

  'subscription_order_invoice.notification'       => 'We are pleased to provide the download link for your <a href=":invoice_pdf" download><b>invoice pdf</b></a> for your subscription of the <b>:plan_name</b>. You can also download the invoice from our :customer_portal_link.',
  'subscription_order_invoice.summary'            => 'Below is a summary of your Order & Subscription:',

  'subscription_order_refunded.notification'      => 'This is to confirm that your refund for the order #:order_id to the <b>:plan_name</b> has been processed. The total refund amount is <b>:currency :refund_total</b>. Please note that it may take a few days for the refund to appear on your account.',
  'subscription_order_refunded.summary'           => 'Below is a summary of your Refunded Order & Subscription:',

  'subscription_order_refund_failed.notification' => 'Unfortunately, we are unable to process your refund request (amount: :amount) for the order #:order_id to the <b>:plan_name</b> subscription. Please contact our support team for assistance.',
  'subscription_order_refund_failed.summary'      => 'Below is a summary of your order to Refund:',

  'subscription_cancel.notification'              => 'We’re sorry to see you go! Your <b>:plan_name</b> subscription was cancelled as per your request. You can still access your benefits until your subscription is terminated on <b>:end_date</b>.<br /><br />Thank you for your past support, and please feel free to contact us if you have any questions or require further assistance.',
  'subscription_cancel.notification_free_trial'   => 'We’re sorry to see you go! Your <b>:plan_name</b> subscription was cancelled and terminated as per your request.',
  'subscription_cancel.summary'                   => 'Below is a summary of your Cancelled Subscription:',

  'subscription_cancel_refund.notification'       => 'We’re sorry to see you go! Your <b>:plan_name</b> subscription was cancelled as per your request.<br /><br />As you selected to receieve a refund, a refund request has been submitted. Once processed, you will receive a refund confirmation email.<br /><br />Thank you for your past support, and please feel free to contact us if you have any questions or require further assistance.',
  'subscription_cancel_refund.summary'            => 'Below is a summary of your cancelled subscription:',

  'subscription_extended.notification'            => 'We are pleased to confirm that your subscription to the <b>:plan_name</b> has been successfully renewed! You can continue enjoying all of the exclusive benefits and features of your subscription.',
  'subscription_extended.summary'                 => 'Below is a summary of your Order & Subscription:',
  'subscription_extended.agreement_claim'         => 'You have agreed to the subscription terms unless you proceed to cancel your subscription.',

  'subscription_failed.notification'              => 'We are writing to inform you that the renewal charge for your <b>:plan_name</b> subscription has failed and your subscription has been terminated.<br /><br />We apologise for the inconvenience caused and request that you kindly repurchase the software if you wish to continue using the product. Alternatively, you may contact our support team for assistance.',
  'subscription_failed.summary'                   => 'Below is a summary of your Failed Subscription:',

  'subscription_invoice_pending.notification'     => 'We regret to inform you that we could not process the payment for your <b>:plan_name</b> subscription<br /><br /> To prevent any disruption to your subscription access, we kindly request that you verify your registered payment method and ensure sufficient funds are available.<br /><br /> If you require any assistance or have any questions regarding your payment, please contact our support team.',
  'subscription_invoice_pending.summary'          => 'Below is a summary of your Subscription:',

  'subscription_reminder.notification'            => 'We would like to remind you that your subscription to <b>:plan_name</b> is scheduled to renew on or after <b>:next_invoice_date</b>.<br /><br />To ensure uninterrupted access to all of your subscription benefits, please ensure that your registered payment method has sufficient funds for the renewal amount.',
  'subscription_reminder.notification_convert'    => 'We would like to remind you that your subscription will be converted from <b>:old_plan_name</b> to <b>:new_plan_name</b> on or after <b>:next_invoice_date</b>.<br /><br />To ensure uninterrupted access to all of your subscription benefits, please ensure that your registered payment method has sufficient funds for the renewal amount.',
  'subscription_reminder.summary'                 => 'Below is a summary of your Subscription:',

  'subscription_renew_required.notification'      => 'We would like to remind you to renew your subscription to <b>:plan_name</b> before <b>:expire_date</b>. You can submit renewal request <b>:renew_link</b> and following the instructions on the page. <br /><br />Please be aware that your subscription will be automatically cancelled if you do not submit the renewal request in a timely manner.',
  'subscription_renew_required.summary'           => 'Below is a summary of your Subscription to renew:',

  'subscription_renew_req_confirmed.notification' => 'We are writing to inform you that the renew request of your subscription to <b>:plan_name</b> has been confirmed. Your subsciption is scheduled to renew on or after <b>:next_invoice_date</b>. <br /><br />To ensure uninterrupted access to all of your subscription benefits, please ensure that your registered payment method has sufficient funds for the renewal amount.',
  'subscription_renew_req_confirmed.summary'      => 'Below is a summary of your Subscription:',

  'subscription_renew_expired.notification'       => 'We are writing to inform you that your subscription to <b>:plan_name</b> was canceled because you did not renew it explicitly. You can still access your benefits until your subscription is terminated on <b>:end_date</b>.<br /><br />Thank you for your past support, and please feel free to contact us if you have any questions or require further assistance.',
  'subscription_renew_expired.summary'            => 'Below is a summary of your Subscription:',

  'subscription_terminated.notification'          => 'We are writing to inform you that your subscription to <b>:plan_name</b> was terminated on <b>:end_date</b> as per your cancellation request.',
  'subscription_terminated.summary'               => 'Below is a summary of your Subscription:',

  'subscription_terms_changed.notification'       => 'We are writing to inform you that the <b>:terms</b> of your subscription to <b>:plan_name</b> have changed.<br /><br />Here is the summury of changes:<br /> :terms_items',
  'subscription_terms_changed.summary'            => 'Below is a summary of your most recent Order & Subscription:',

  'subscription_plan_updated_german.notification' => 'We are writing to inform you that the renewal policy of your subscription to <b>:plan_name</b> was updated.<br /><br />Your next invoice period will remain the current annual plan. However, you are required to manually renew your subscription before it expires on <b>:end_date</b>. If you do not renew your subscription explicitly, it will be cancelled automatically when it expires. We will send you a reminder notification to you 30 days before the expiration.<br /><br />Thank you for your understanding. If you have any questions, please feel free to contact us.',
  'subscription_plan_updated_german.summary'      => 'Below is a summary of your updated Subscription:',

  'subscription_plan_updated_other.notification'  => 'We are writing to inform you that the renewal policy of your subscription to <b>:plan_name</b> was updated.<br /><br />Instead of being converted to monthly plan after it expires, your annual subscription will be automatically renewed.<br /><br />Thank you for your understanding. If you have any questions, please feel free to contact us.',
  'subscription_plan_updated_other.summary'       => 'Below is a summary of your updated Subscription:',
];
