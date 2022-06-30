<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TestController extends Controller
{
  public function resetData()
  {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed');

    return response()->json(['message' => 'database reset successfully!']);
  }
}
