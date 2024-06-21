<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;

/**
 * admin can access any resource
 */
const resourceRoleMapping = [
  'admin-user.list' => [],
  'admin-user.get' => [],
  'admin-user.create' => [],
  'admin-user.update' => [],
  'admin-user.delete' => [],

  'country.list' => [],
  'country.create' => [],
  'country.update' => [],
  'country.delete' => [],

  'coupon.list' => [],
  'coupon.get' => [],
  'coupon.create' => [],
  'coupon.update' => [],
  'coupon.delete' => [],

  'design-plan.list' => [],
  'design-plan.get' => [],
  'design-plan.create' => [],
  'design-plan.update' => [],
  'design-plan.delete' => [],

  'config.update' => [],

  'license-package.list' => [],
  'license-package.get' => [],
  'license-package.create' => [],
  'license-package.update' => [],
  'license-package.delete' => [],

  'license-sharing.list' => [],
  'license-sharing.get' => [],

  'license-sharing-invitation.list' => [],
  'license-sharing-invitation.get' => [],

  'machine.create' => ['siser-backend' => 1],
  'machine.update' => ['siser-backend' => 1],
  'machine.delete' => ['siser-backend' => 1],
  'machine.transfer' => ['siser-backend' => 1],

  'software-package.create' => [],
  'software-package.update' => [],
  'software-package.delete' => [],

  'subscription.list' => [],
  'subscription.get' => [],
  'subscription.cancel' => [],
  'subscription.stop' => [],

  'user.list' => ['siser-backend' => 1],
  'user.get' => ['siser-backend' => 1],
  'user.create' => ['siser-backend' => 1],
  'user.refresh' => ['siser-backend' => 1],
  'user.update' => [],

  'user.billing-info.get' => [],
  'user.payment-method.get' => [],
  'user.lds-license.get' => [],
  'user.machine.list' => [],
  'user.subscription.list' => [],

  'invoice.list' => [],
  'invoice.get' => [],
  'invoice.cancel' => [],

  'refund.list' => [],
  'refund.get' => [],
  'refund.create' => [],

  'x-ray.summary' => ['siser-backend' => 1],
];


class CheckAccess
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
   */
  public function handle(Request $request, Closure $next, string $resource)
  {
    // unknown resource
    if (!isset(resourceRoleMapping[$resource])) {
      return response()->json(['message' => 'Forbidden'], 401);
    }

    /** @var AdminUser $user  */
    $user = auth('admin')->user();
    if (!$user->roles) {
      return response()->json(['message' => 'Forbidden'], 401);
    }

    // check each role of the user
    foreach ($user->roles as $role) {
      if ($role == 'admin') {
        // admin can do anything
        return $next($request);
      }

      if (isset(resourceRoleMapping[$resource][$role])) {
        return $next($request);
      }
    }

    return response()->json(['message' => 'Forbidden'], 403);
  }
}
