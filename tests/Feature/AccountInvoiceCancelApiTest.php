<?php

namespace Tests\Feature;

class AccountInvoiceCancelApiTest extends AccountInvoiceTestCase
{
  public string $baseUrl = '/api/v1/account/invoices';
  public string $model = Invoice::class;

  public ?string $role = 'customer';

  public function testAccountInvoiceCancelOk()
  {
  }

  public function testMore()
  {
  }
}
