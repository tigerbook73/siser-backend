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

  'config.update' => [],

  'machine.create' => ['siser-backend' => 1],
  'machine.update' => ['siser-backend' => 1],
  'machine.delete' => ['siser-backend' => 1],
  'machine.transfer' => ['siser-backend' => 1],

  'software-package.create' => [],
  'software-package.update' => [],
  'software-package.delete' => [],

  'user.create' => ['siser-backend' => 1],
  'user.refresh' => ['siser-backend' => 1],

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
