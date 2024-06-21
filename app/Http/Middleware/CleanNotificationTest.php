<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Test\LicenseSharingNotificationTest;
use App\Http\Controllers\Test\SubscriptionNotificationTest;
use Closure;
use Illuminate\Http\Request;

class CleanNotificationTest
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next)
  {
    return $next($request);
  }

  public function terminate($request, $response)
  {
    SubscriptionNotificationTest::clean();
    LicenseSharingNotificationTest::clean();
  }
}
