<?php

namespace App\Http\Controllers;

use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
  public function __construct(public SubscriptionManager $manager)
  {
  }

  public function handler(Request $request)
  {
    $inputs = $request->all();
    if (!$this->manager->webhookHandler($inputs)) {
      return response()->json(null, status: 400);
    } else {
      return response()->json(null);
    }
  }
}
