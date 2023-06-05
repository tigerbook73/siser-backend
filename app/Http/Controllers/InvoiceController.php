<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends SimpleController
{
  protected string $modelClass = Invoice::class;

  protected function getListRules(array $inputs = []): array
  {
    return [
      'id'              => ['filled'],
      'user_id'         => ['filled'],
      'subscription_id' => ['filled'],
    ];
  }

  public function accountList(Request $request)
  {
    $this->validateUser();
    $request->merge(['user_id' => $this->user->id]);
    return static::list($request);
  }

  public function accountIndex(int $id)
  {
    $this->validateUser();

    $invoice = $this->baseQuery()->where('user_id', $this->user->id)->findOrFail($id);
    return $this->transformSingleResource($invoice);
  }
}
