<?php

namespace App\Http\Controllers;

use App\Services\Paddle\SubscriptionManagerPaddle;
use Http\Discovery\Psr17Factory;
use Illuminate\Http\Request;
use Paddle\SDK\Notifications\Secret;
use Paddle\SDK\Notifications\Verifier;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class PaddleWebhookController extends Controller
{
  public function __construct(public SubscriptionManagerPaddle $manager) {}

  public function check()
  {
    return response()->json(['message' => 'paddle check ok']);
  }

  public function handler(Request $request)
  {
    $psr17Factory = new Psr17Factory();
    $psrRequest = (new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory))
      ->createRequest($request);

    $result = (new Verifier())->verify(
      $psrRequest,
      new Secret(config('paddle.webhook_secret'))
    );

    if (!$result) {
      return response()->json(['message' => 'paddle verify failed'], 401);
    }

    $inputs = $request->all();
    return $this->manager->webhookHandler($inputs);
  }
}
