<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Http\Request;

class InvoiceController extends SimpleController
{
  protected string $modelClass = Invoice::class;

  public function __construct(public SubscriptionManager $manager)
  {
    parent::__construct();
  }

  protected function getListRules(array $inputs = []): array
  {
    return [
      'id'              => ['filled'],
      'user_id'         => ['filled'],
      'subscription_id' => ['filled'],
      'order_only'      => ['filled', 'boolean'],
    ];
  }

  public function accountList(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'subscription_id'   => ['filled', 'integer'],
      'order_only'        => ['filled', 'boolean'],
    ]);

    $query =  Invoice::where('user_id', $this->user->id)->limit(100);
    if (isset($inputs['subscription_id'])) {
      $query->where('subscription_id', $inputs['subscription_id']);
    }
    if (isset($inputs['order_only'])) {
      $query->where('period', '<=', 1);
    }
    $invoices = $query->get();
    return ['data' => $this->transformMultipleResources($invoices)];
  }

  public function accountIndex(int $id)
  {
    $this->validateUser();

    $invoice = $this->baseQuery()->where('user_id', $this->user->id)->findOrFail($id);
    return response()->json($this->transformSingleResource($invoice));
  }

  protected function commonCancel(Invoice $invoice)
  {
    if (!$invoice->isCancellable()) {
      return response()->json(['message' => 'only pending subscription order can be cancelled'], 409);
    }

    try {
      $invoice = $this->manager->cancelOrder($invoice);
      return response()->json($this->transformSingleResource($invoice));
    } catch (\Throwable $th) {
      return response()->json(['message' => 'only pending subscription order can be cancelled'], 409);
    }
  }

  public function accountCancel(int $id)
  {
    $this->validateUser();

    /** @var Invoice $invoice */
    $invoice = Invoice::where('user_id', $this->user->id)
      ->findOrFail($id);

    return $this->commonCancel($invoice);
  }

  public function cancel(int $id)
  {
    $this->validateUser();

    /** @var Invoice $invoice */
    $invoice = Invoice::findOrFail($id);

    return $this->commonCancel($invoice);
  }
}
