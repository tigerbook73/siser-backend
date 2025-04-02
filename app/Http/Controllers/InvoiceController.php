<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Paddle\SubscriptionManagerPaddle;
use Illuminate\Http\Request;

class InvoiceController extends SimpleController
{
  protected string $modelClass = Invoice::class;
  protected string $orderDirection = 'desc';

  public function __construct(
    public SubscriptionManagerPaddle $manager,
  ) {
    parent::__construct();
  }

  protected function getListRules(array $inputs = []): array
  {
    return [
      'id'              => ['filled'],
      'user_id'         => ['filled'],
      'subscription_id' => ['filled'],
      'type'            => ['filled'],
    ];
  }

  /**
   * GET /account/invoices
   */
  public function accountList(Request $request)
  {
    $request->merge(['user_id' => auth('api')->id()]);
    return $this->list($request);
  }

  /**
   * GET /account/invoices/{id}
   */
  public function accountIndex(int $id)
  {
    $this->validateUser();

    $invoice = $this->baseQuery()->where('user_id', $this->user->id)->findOrFail($id);
    return response()->json($this->transformSingleResource($invoice));
  }

  /**
   * API for paddles
   */

  /**
   * get invoice's pdf
   */
  protected function getTransactionInvoicePDF(Invoice $invoice)
  {
    if (!$invoice->isCompleted()) {
      return response()->json(['message' => 'invoice is not completed'], 400);
    }

    if (!$invoice->getMeta()->paddle->transaction_id) {
      return response()->json(['message' => 'invoice does not have transaction id'], 400);
    }

    try {
      $invoiceUrl = $this->manager->transactionService->getInvoicePDF($invoice->getMeta()->paddle->transaction_id);
      return response()->json(['url' => $invoiceUrl]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], 400);
    }
  }

  /**
   * GET /invoices/{id}/pdf
   */
  public function getInvoicePdf(int $id)
  {
    return $this->getTransactionInvoicePDF(Invoice::findOrFail($id));
  }


  /**
   * GET /account/invoices/{id}/pdf
   */
  public function accountGetInvoicePdf(int $id)
  {
    $this->validateUser();

    return $this->getTransactionInvoicePDF(
      Invoice::where('user_id', $this->user->id)->findOrFail($id)
    );
  }
}
