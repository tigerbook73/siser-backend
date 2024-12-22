<x-emails.subscription.layout-new
  :$type
  :$subscription
  :$helper
>

  {!!
    $helper->trans(
      'welcome_back_for_renew.notification',
      [
        'expires_at' => $helper->formatDate($subscription->end_date),
      ]
    )
  !!}

</x-emails.subscription.layout-new>
