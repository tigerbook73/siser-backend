<?php

namespace App\Http\Controllers;

use App\Services\DigitalRiver\DigitalRiverService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
  public function __construct(public DigitalRiverService $drService)
  {
  }

  public function handler(Request $request)
  {
    $inputs = $request->all();
    if (!$this->drService->webhookHandler($inputs)) {
      return response(status: 400);
    }
  }
}
