<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\LicensePackage;
use App\Models\Subscription;
use App\Models\User;
use App\Services\DigitalRiver\SubscriptionManager;
use App\Services\Paddle\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends SimpleController
{
  protected string $modelClass = Invoice::class;

  public function __construct(
    public SubscriptionManager $manager,
    public TransactionService $transactionService
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

  protected function getCreateRules(array $inputs = []): array
  {
    return [
      'subscription_id'     => ['required'],
      'type'                => ['required', Rule::in([Invoice::TYPE_NEW_LICENSE_PACKAGE, Invoice::TYPE_INCREASE_LICENSE])],
      'license_package_id'  => ['required_if:type,' . Invoice::TYPE_NEW_LICENSE_PACKAGE],
      'license_count'       => ['required'],
    ];
  }

  protected function getPayRules(array $inputs = []): array
  {
    return [
      'dr_source_id'  => ['required'],
    ];
  }

  public function accountList(Request $request)
  {
    $request->merge(['user_id' => auth('api')->id()]);
    return $this->list($request);
  }

  public function accountCreate(Request $request)
  {
    $this->validateUser();
    $inputs = $request->validate($this->getCreateRules());

    if ($this->user->type == User::TYPE_BLACKLISTED) {
      return response()->json(['message' => 'User are blocked, please contact our support team.'], 400);
    }

    $subscription = Subscription::find($inputs['subscription_id']);
    if (!$subscription) {
      return response()->json(['message' => 'subscription not found'], 404);
    }

    try {
      if ($inputs['type'] === Invoice::TYPE_NEW_LICENSE_PACKAGE) {
        $licensePackage = LicensePackage::find($inputs['license_package_id']);
        if (!$licensePackage) {
          return response()->json(['message' => 'license package not found'], 404);
        }

        $invoice = $this->manager->createNewLicensePackageInvoice($subscription, $licensePackage, $inputs['license_count']);
      } else {
        $invoice = $this->manager->createIncreaseLicenseInvoice($subscription, $inputs['license_count']);
      }
      return response()->json($this->transformSingleResource($invoice));
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], 409);
    }
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
      $invoice = $this->manager->cancelInvoice($invoice);
      return response()->json($this->transformSingleResource($invoice));
    } catch (\Throwable $th) {
      return response()->json(['message' => 'only pending subscription order can be cancelled'], 409);
    }
  }

  public function accountDelete(int $id)
  {
    $this->validateUser();

    /** @var Invoice $invoice */
    $invoice = Invoice::where('user_id', $this->user->id)
      ->whereIn('type', [Invoice::TYPE_NEW_LICENSE_PACKAGE, Invoice::TYPE_INCREASE_LICENSE])
      ->where('status', Invoice::STATUS_INIT)
      ->findOrFail($id);

    return $this->manager->deleteInvoice($invoice);
  }

  public function accountCancel(int $id)
  {
    $this->validateUser();

    /** @var Invoice $invoice */
    $invoice = Invoice::where('user_id', $this->user->id)
      ->findOrFail($id);

    return $this->commonCancel($invoice);
  }

  public function accountPay(Request $request, int $id)
  {
    $this->validateUser();

    $inputs = $request->validate($this->getPayRules());

    /** @var Invoice $invoice */
    $invoice = Invoice::where('user_id', $this->user->id)
      ->where('status', Invoice::STATUS_INIT)
      ->findOrFail($id);

    $invoice = $this->manager->payLicensePackageInvoice($invoice, $inputs['dr_source_id']);
    return response()->json($this->transformSingleResource($invoice));
  }

  public function cancel(int $id)
  {
    $this->validateUser();

    /** @var Invoice $invoice */
    $invoice = Invoice::findOrFail($id);

    return $this->commonCancel($invoice);
  }

  /**
   * API for paddles
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
      $invoiceUrl = $this->transactionService->getInvoicePDF($invoice->getMeta()->paddle->transaction_id);
      return response()->json(['url' => $invoiceUrl]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], 400);
    }
  }

  public function getInvoicePdf(int $id)
  {
    return $this->getTransactionInvoicePDF(Invoice::findOrFail($id));
  }

  public function accountGetInvoicePdf(int $id)
  {
    $this->validateUser();

    return $this->getTransactionInvoicePDF(
      Invoice::where('user_id', $this->user->id)->findOrFail($id)
    );
  }
}
