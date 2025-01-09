<?php

namespace Tests\Feature;

class AccountInvoiceGetApiTest extends AccountInvoiceTestCase
{
  public ?string $role = 'customer';

  public function testAccountInvoiceGetSuccess()
  {
    $this->markTestIncomplete('more test cases to come');
  }

  public function testAccountInvoiceGetError()
  {
    $this->getAssert(404, 999999999999999999);
  }
}
