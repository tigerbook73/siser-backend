<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;

class CheckRole
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next, string $roleText)
  {
    /** @var AdminUser $user  */
    $user = auth('admin')->user();
    if (!$user->roles) {
      return response()->json(['message' => 'Forbidden'], 401);
    }

    // generate $userRoles
    foreach ($user->roles as $role) {
      $userRoles[$role] = true;
    }

    $roles = explode('|', $roleText);
    foreach ($roles as $role) {
      if ($userRoles[$role]) {
        return $next($request);
      }
    }

    return response()->json(['message' => 'Forbidden'], 401);
  }
}
