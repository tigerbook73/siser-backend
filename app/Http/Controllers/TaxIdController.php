<?php

namespace App\Http\Controllers;

use App\Models\TaxId;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Http\Request;

class TaxIdController extends SimpleController
{
  protected string $modelClass = TaxId::class;


  public function __construct(public SubscriptionManager $manager)
  {
    parent::__construct();
  }

  public function accountList()
  {
    $this->validateUser();

    $taxIds = TaxId::where('user_id', $this->user->id)->get();
    return ['data' => $this->transformMultipleResources($taxIds)];
  }

  public function accountIndex(string $id)
  {
    $this->validateUser();

    $taxId = TaxId::where('user_id', $this->user->id)->where('id', $id)->firstOrFail();
    return $this->transformSingleResource($taxId);
  }

  public function accountCreate(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'type' => ['required', 'string', 'max:255'],
      'value' => ['required', 'string', 'max:255'],
    ]);

    $taxId = $this->manager->createTaxId($this->user, $inputs['type'], $inputs['value']);
    return $this->transformSingleResource($taxId);
  }

  public function accountDelete($id)
  {
    $this->validateUser();

    $taxId = TaxId::where('user_id', $this->user->id)->where('id', $id)->firstOrFail();
    return $this->manager->deleteTaxId($taxId);
  }
}
