<?php

namespace Tests\Feature;

class AccountInvoiceListApiTest extends AccountInvoiceTestCase
{
  public ?string $role = 'customer';

  public function testAccountInvoiceListOk()
  {
    $this->listAssert(200, [], 0);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
