<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends SimpleController
{
  protected string $modelClass = Invoice::class;


  /**
   * TODO: MOCKUP
   */

  public $mockData = [
    [
      "id" => 1,
      "user_id" => 1,
      "subscription_id" => 1,
      "period" => 1,
      "currency" => "USD",
      "amount" => 9.9,
      "discount" => 4.95,
      "processing_fee" => 0,
      "tax" => 0,
      "total_amount" => 4.95,
      "invoice_date" => "2023-01-25",
      "close_date" => "2023-01-25",
      "pdf_file" => "to be discussed",
      "status" => "draft"
    ]
  ];

  public function list(Request $request)
  {
    return response()->json([
      "data" => $this->mockData
    ]);
  }

  public function index(int $id)
  {
    $found = null;
    foreach ($this->mockData as $item) {
      if ($item['id'] == $id) {
        $found = $item;
      }
    }

    if (!$found) {
      return response()->json(null, 404);
    }
    return response()->json($found);
  }

  public function listByAccount(Request $request)
  {
    return $this->list($request);
  }

  public function indexByAccount(int $id)
  {
    return $this->index($id);
  }
}
