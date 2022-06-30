<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminUserController extends SimpleController
{
  protected string $modelClass = AdminUser::class;
}
