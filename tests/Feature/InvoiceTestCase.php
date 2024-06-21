<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Tests\ApiTestCase;
use Tests\Models\Invoice as ModelsInvoice;

class InvoiceTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/invoices';
  public string $model = Invoice::class;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsInvoice);
    unset($this->modelSchema[array_search('license_package_info', $this->modelSchema)]);
    unset($this->modelSchema[array_search('items', $this->modelSchema)]);
  }
}
