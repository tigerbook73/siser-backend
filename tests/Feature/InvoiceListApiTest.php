<?php

namespace Tests\Feature;

class InvoiceListApiTest extends InvoiceTestCase
{
  public ?string $role = 'admin';

  public function testInvoiceListOk()
  {
    $this->listAssert(200, [], 0);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
