<?php

namespace Tests\Feature;

use App\Models\Invoice;

class AccountInvoiceCancelApiTest_ extends AccountInvoiceTestCase
{
  public string $baseUrl = '/api/v1/account/invoices';
  public string $model = Invoice::class;

  public ?string $role = 'customer';

  public function testAccountInvoiceCancelOk()
  {
    $this->markTestIncomplete('This test has not been implemented yet.');
  }
}
