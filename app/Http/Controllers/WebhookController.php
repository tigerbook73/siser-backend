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
    return $this->manager->webhookHandler($inputs);
  }
}
