<x-emails.license-sharing.layout
  :$type
  :$invitation
  :$helper
>
{!!
  $helper->trans(
    'license_sharing_invitation_revoked.notification',
    [
      'product_name' => $invitation->product_name,
      'owner_name'   => $invitation->owner_name,
      'owner_email'  => $invitation->owner_email,
      'customer_portal_link' => $helper->getCustomerPortalLink(),
    ]
  )
!!}

</x-emails.license-sharing.layout>
