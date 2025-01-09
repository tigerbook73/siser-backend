<?php

namespace Tests\Feature;

class InvoiceListApiTest extends InvoiceTestCase
{
  public ?string $role = 'admin';

  public function testInvoiceListOk()
  {
    $this->listAssert(200, [], 0);
  }
}
