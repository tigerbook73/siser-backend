<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Refund;
use App\Services\Paddle\SubscriptionManagerPaddle;
use App\Services\RefundRules;
use Illuminate\Http\Request;

class RefundController extends SimpleController
{
  protected string $modelClass = Refund::class;

  public function __construct(private SubscriptionManagerPaddle $manager)
  {
    parent::__construct();
  }

  protected function getListRules(array $inputs = []): array
  {
    return [
      'id'              => ['filled'],
      'user_id'         => ['filled'],
      'subscription_id' => ['filled'],
      'invoice_id'      => ['filled'],
    ];
  }

  protected function getCreateRules(array $inputs = []): array
  {
    return [
      'invoice_id'      => ['required', 'exists:invoices,id'],
      'amount'          => ['required', 'decimal:0,2'],
      'reason'          => ['required', 'string', 'max:255'],
    ];
  }

  /**
   * GET /account/refunds
   */
  public function accountList(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'subscription_id'   => ['filled', 'integer'],
      'invoice_id'        => ['filled', 'integer'],
    ]);

    $query =  Refund::where('user_id', $this->user->id)->limit(100);
    if (isset($inputs['subscription_id'])) {
      $query->where('subscription_id', $inputs['subscription_id']);
    }
    if (isset($inputs['invoice_id'])) {
      $query->where('invoice_id', $inputs['invoice_id']);
    }
    $refunds = $query->get();
    return ['data' => $this->transformMultipleResources($refunds)];
  }

  /**
   * GET /account/refunds/{id}
   */
  public function accountIndex(int $id)
  {
    $this->validateUser();

    $refund = $this->baseQuery()->where('user_id', $this->user->id)->findOrFail($id);
    return $this->transformSingleResource($refund);
  }

  // GET /refunds
  // default implementation

  // GET /refunds/{id}
  // default implementation

  /**
   * POST /refunds
   */
  public function create(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateCreate($request);

    /** @var Invoice $invoice */
    $invoice = Invoice::findOrFail($inputs['invoice_id']);

    // check refundable
    $result = RefundRules::invoiceRefundable($invoice);
    if (!$result->isRefundable()) {
      return response()->json(['message' => $result->getReason()], 400);
    }

    // check amount
    if ($inputs['amount'] <= 0 || $inputs['amount'] > $result->getRefundableAmount()) {
      return response()->json(['message' => 'amount must be greater than 0 and less or equal than total refundable'], 400);
    }

    // create refund, TODO:
    try {
      $refund = $this->manager->adjustmentService->createRefundDirectly(
        $invoice,
        $inputs['amount'],
        $inputs['reason']
      );
      return  response()->json($this->transformSingleResource($refund));
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }
}
