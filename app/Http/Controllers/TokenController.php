<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TokenController extends Controller
{
  /**
   * create a new token for current login user
   */
  public function create(Request $request)
  {
    // TODO: validation
    // $request->validate([
    //   'token_name' => ['required', 'string']
    // ]);

    // create token
    $token = $request->user()->createToken($request->token_name);
    return ['token' => $token->plainTextToken];
  }
}
