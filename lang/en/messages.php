<?php

return [
  // english

  // layout
  'layout.greeting'                               => 'Dear :name,',
  'layout.manage_subscription'                    => 'You can view and/or manage your subscriptions and/or orders from our :customer_portal_link.',
  'layout.faqs'                                   => 'For questions regarding cancellations and refunds, please visit our :support_link.',
  'layout.contact_us'                             => 'Please contact us via email :support_email_link, or visit our :customer_support_link if you require support.',
  'layout.regards'                                => 'Thank You,',

  // license sharing
  'license_sharing_new_invitation.notification'       => 'We are writing to inform you that you have received a license sharing invitation for <b>:product_name</b> from <b>:owner_name (:owner_email)</b>.<br /><br />You can view and/or accept the invitation from our :customer_portal_link.',
  'license_sharing_invitation_expired.notification'   => 'We are writing to inform you that your license sharing for <b>:product_name</b> from <b>:owner_name (:owner_email)</b> is expired.<br /><br />You can view your current subscription status from our :customer_portal_link.',
  'license_sharing_invitation_cancelled.notification' => 'We are writing to inform you that your license sharing for <b>:product_name</b> from <b>:owner_name (:owner_email)</b> is cancelled.<br /><br />You can view your current subscription status from our :customer_portal_link.',
  'license_sharing_invitation_revoked.notification'   => 'We are writing to inform you that your license sharing for <b>:product_name</b> from <b>:owner_name (:owner_email)</b> is cancelled by the owner.<br /><br />You can view your current subscription status from our :customer_portal_link.',

  // welcome back for renew
  'welcome_back_for_renew.notification' => '
    We would like to inform you that we are moving Leonardo® Design Studio Pro to a new payment processor. This transition will allow us to continue accepting various payment methods, such as PayPal and Google Pay, in addition to credit cards. <br /><br />
    As part of this transition, your Leonardo Pro subscription will automatically be cancelled at <b>:expires_at</b>. You will need to re-subscribe manually before the expires date to continue accessing Pro features. <br /><br />
    To re-subscribe to Leonardo Pro, please follow the steps below: <br />
    <ol>
      <li>Go to <a href="https://software.siser.com/account/subscription">https://software.siser.com</a> and sign in to your Leonardo account.</li>
      <li>Select “RENEW SUBSCRIPTION” and fill out your billing info</li>
      <li>On the next page, choose either the Monthly or Annual plan</li>
      <li>On the checkout page, select your payment method or enter your credit card information and complete the purchase</li>
    </ol>

    We apologize for any inconvenience this transition may cause. As a token of our appreciation for your patience and understanding, we’d like to offer you a 25% discount on either a Monthly or Annual subscription to Leonardo Pro. <br /> <br />
    To claim this discount, please click “Add Discount” on the checkout screen and enter promo code <b style="color: red;">WELCOMEBACK</b>. You will see the 25% discount automatically applied to your purchase total.
    Thank you for your continued support of Siser® and Leonardo Design Studio. We’re looking forward to seeing what you create next!
  ',

  // welcome back for failed
  'welcome_back_for_failed.notification' => '
    We are writing to inform you that your subscription has been terminated. <br /><br />
    We’d like to offer you a 25% discount on either a Monthly or Annual subscription to Leonardo Pro. <br /> <br />
    To claim this discount, please click “Add Discount” on the checkout screen and enter promo code <b style="color: red;">WELCOMEBACK</b>. You will see the 25% discount automatically applied to your purchase total.
    Thank you for your continued support of Siser® and Leonardo Design Studio. We’re looking forward to seeing what you create next!
  ',
];
