<?php

/**
 * UserCreateReq
 */

namespace App\Mockup\Models;

/**
 * UserCreateReq
 */
class UserCreateReq
{

  /** @var string $create_from */
  public $create_from = "";

  /** @var string $access_token user&#39;s valid cognito access token, required only when create_from &#x3D; &#39;access_token&#39;*/
  public $access_token = "";

  /** @var string $username user&#39;s valid cognito username, required only when create_from &#x3D; &#39;username&#39;*/
  public $username = "";
}
