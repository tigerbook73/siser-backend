<?php

namespace Tests\Feature;

class AccountInvoiceCancelApiTest extends AccountInvoiceTestCase
{
  public string $baseUrl = '/api/v1/account/invoices';
  public string $model = Invoice::class;

  public ?string $role = 'customer';

  public function testAccountInvoiceCancelOk()
  {
    $this->markTestIncomplete('This test has not been implemented yet.');
  }

  public function testMore()
  {
    $this->markTestIncomplete('More ...');
  }
}
