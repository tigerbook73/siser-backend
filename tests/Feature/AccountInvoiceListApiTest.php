<?php

namespace Tests\Feature;

class AccountInvoiceListApiTest extends AccountInvoiceTestCase
{
  public ?string $role = 'customer';

  public function testAccountInvoiceListOk()
  {
    $this->listAssert(200, [], 0);
  }
}
