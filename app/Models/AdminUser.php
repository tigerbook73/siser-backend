<?php

namespace App\Models;

use App\Models\User;

class AdminUser extends User
{
  public function newQuery()
  {
    return parent::newQuery()->whereNull('cognito_id');
  }
}
