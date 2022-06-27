<?php

namespace App\Models;

use App\Models\User;

class EndUser extends User
{
  public function newQuery()
  {
    return parent::newQuery()->whereNotNull('cognito_id');
  }
}
