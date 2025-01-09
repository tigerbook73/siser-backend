<?php

namespace Tests\Feature;

class InvoiceGetApiTest extends InvoiceTestCase
{
  public ?string $role = 'admin';

  public function testInvoiceGetSuccess()
  {
    $this->markTestIncomplete('more test cases to come');
  }

  public function testInvoiceGetError()
  {
    $this->getAssert(404, 999999999999999999);
  }
}
